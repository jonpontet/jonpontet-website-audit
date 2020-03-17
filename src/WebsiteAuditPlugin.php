<?php
namespace JonPontet\WebsiteAudit;

use \JonPontet\WebsiteAudit\Core\Settings;
use \JonPontet\WebsiteAudit\Shortcode\Recent as RecentShortcode;
use \JonPontet\WebsiteAudit\Shortcode\Result as ResultShortcode;
use \JonPontet\WebsiteAudit\Shortcode\ResultDomain as ResultDomainShortcode;
use \JonPontet\WebsiteAudit\Shortcode\Form as FormShortcode;
use \JonPontet\WebsiteAudit\Shortcode\Email as EmailShortcode;
use \JonPontet\WebsiteAudit\Core\Auditor;
use \JonPontet\WebsiteAudit\Core\Result;
use \JonPontet\WebsiteAudit\Core\RateLimiter;

class WebsiteAuditPlugin {

  public static $instance;
  public static $auditor;
  public static $settings;
  public static $result;
  public static $rateLimiter;
  public static $logFile;
  public static $logFileDir;

  public function __construct() {
    if (self::$instance) {
      return self::$instance;
    }
    
    $wpUploadDir = wp_upload_dir();
    self::$logFileDir = $wpUploadDir['basedir'] . '/jpwa-logs/';
    self::$logFile = self::$logFileDir . 'jpwa.log';

    add_action('tgmpa_register', [$this, 'registerRequiredPlugins']);
    add_action('init', [$this, 'registerShortcodes']);
    add_action('wp_enqueue_scripts', [$this, 'loadAssets']);
    add_action('init', [$this, 'loadTextDomain']);
    add_action('init', [$this, 'registerCustomPost']);
    add_action('init', [$this, 'registerCustomFields']);
    add_action('init', [$this, 'initErrorLogs']);

    self::$instance = $this;
    self::$settings = new Settings();
    self::$rateLimiter = new RateLimiter(self::$settings->get('concurrent_requests'), self::$settings->get('request_expiry_time'));

    if (!self::$settings->get('ssl_verify')) {
      add_filter( 'https_ssl_verify', '__return_false' );
      add_filter( 'https_local_ssl_verify', '__return_false' );
    }

    add_action('wp', [$this, 'tryToHandleRequest']);
    add_action('wp', [$this, 'tryToHandlePermalink']);
    add_action('wp', [$this, 'tryToHandleEmailSend']);

    if (!self::$settings->get('ssl_verify')) {
      remove_filter( 'https_ssl_verify', '__return_false' );
      remove_filter( 'https_local_ssl_verify', '__return_false' );
    }
  }
  
  public function initErrorLogs() {

    $files = array(
        array(
            'base' => self::$logFileDir,
            'file' => '.htaccess',
            'content' => 'deny from all',
        ),
        array(
            'base' => self::$logFileDir,
            'file' => 'index.html',
            'content' => '',
        ),
    );

    foreach ( $files as $file ) {
        if ( wp_mkdir_p( $file['base'] ) && ! file_exists( trailingslashit( $file['base'] ) . $file['file'] ) ) {
            $file_handle = @fopen( trailingslashit( $file['base'] ) . $file['file'], 'w' ); // phpcs:ignore Generic.PHP.NoSilencedErrors.Discouraged, WordPress.WP.AlternativeFunctions.file_system_read_fopen
            if ( $file_handle ) {
                fwrite( $file_handle, $file['content'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fwrite
                fclose( $file_handle ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
            }
        }
    }
    
    $file = self::$logFile;
    
    if ( ! file_exists( $file ) ) {
        $temphandle = @fopen( $file, 'w+' ); // @codingStandardsIgnoreLine.
        @fclose( $temphandle ); // @codingStandardsIgnoreLine.

        if ( defined( 'FS_CHMOD_FILE' ) ) {
            @chmod( $file, FS_CHMOD_FILE ); // @codingStandardsIgnoreLine.
        }
    }
  }

  public function registerRequiredPlugins() {
    $plugins = array(
      array(
      'name'               => 'Advanced Custom Fields PRO',
      'slug'               => 'advanced-custom-fields-pro',
      'source'             => JPWA_PLUGIN_ROOT . '/lib/plugins/advanced-custom-fields-pro.zip',
      'required'           => true,
      'version'            => '5.7.12',
      'force_activation'   => true,
      'force_deactivation' => false,
      ),
    );
    $config = array(
      'id'           => 'jpwa',
      'default_path' => '',
      'menu'         => 'emc-install-plugins',
      'parent_slug'  => 'plugins.php',
      'capability'   => 'manage_options',
      'has_notices'  => true,
      'dismissable'  => false,
      'dismiss_msg'  => '',
      'is_automatic' => true,
      'message'      => '',
      'strings'      => array(
      'notice_can_install_required'     => _n_noop(
        /* translators: 1: plugin name(s). */
        'This plugin requires the following plugin: %1$s.',
        'This plugin requires the following plugins: %1$s.',
        'tgmpa'
        ),
        'notice_can_install_recommended'  => _n_noop(
        /* translators: 1: plugin name(s). */
        'This plugin recommends the following plugin: %1$s.',
        'This plugin recommends the following plugins: %1$s.',
        'tgmpa'
        ),
      )
    );
    tgmpa( $plugins, $config );
  }

  public function registerShortcodes() {
    $shortcode = new RecentShortcode();
    $shortcode->register();
    
    $shortcode = new ResultShortcode();
    $shortcode->register();
    
    $shortcode = new ResultDomainShortcode();
    $shortcode->register();

    $shortcode = new FormShortcode();
    $shortcode->register();

    $shortcode = new EmailShortcode();
    $shortcode->register();
  }

  public function loadAssets() {
    global $post;

    if (!$post) {
      return;
    }

    if (!has_shortcode($post->post_content, 'jpwa_form') && !has_shortcode($post->post_content, 'jpwa_result')) {
      return;
    }

    wp_enqueue_script( 'jquery' );
    

    wp_register_style('jpwa-boostrap4', 'https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap-grid.min.css');
    wp_enqueue_style('jpwa-boostrap4');
	
    wp_enqueue_script('jpwa-script', plugins_url('assets/front.js', JPWA_PLUGIN_FILE), [ 'jquery' ], '1.0.7', true );
    

    if (!has_shortcode($post->post_content, 'jpwa_result')) {
      return;
    }

    wp_register_style('jpwa-fa', 'https://use.fontawesome.com/releases/v5.8.2/css/all.css');
    wp_enqueue_style('jpwa-fa');

    wp_register_style('jpwa-tooltipster', plugins_url('assets/tooltipster.bundle.min.css', JPWA_PLUGIN_FILE));
    wp_enqueue_style('jpwa-tooltipster');

    wp_register_style('jpwa-tooltipster-theme', plugins_url('assets/tooltipster-sideTip-punk.min.css', JPWA_PLUGIN_FILE));
    wp_enqueue_style('jpwa-tooltipster-theme');

    wp_register_script('jpwa-tooltipster', plugins_url('assets/tooltipster.bundle.min.js', JPWA_PLUGIN_FILE), [ 'jquery' ], '4.2.6', true );
    wp_enqueue_script('jpwa-tooltipster');
	
    wp_add_inline_script('jpwa-tooltipster', '
		  jQuery(function($) {
				$(".tooltip").tooltipster({
					trigger: "hover",
					side: "right",
					theme: "tooltipster-punk",
                    maxWidth: 350
				});
			});
    ');

    wp_register_script('jpwa-circles', plugins_url('assets/circles.min.js', JPWA_PLUGIN_FILE), [ 'jquery' ], '1.0', true );
    wp_enqueue_script('jpwa-circles');
	
    wp_add_inline_script('jpwa-circles', '
      (function() {
        var eles = document.querySelectorAll(".jpwa-result-score__circle");
        for (var i = 0; i < eles.length; i++) {
          (function(ele) {
            var dataText = ele.getAttribute("data-text");
            var dataValue = ele.getAttribute("data-value");
            var dataColor = ele.getAttribute("data-color");
            var dataBgColor = ele.getAttribute("data-bg-color");

            Circles.create({
              id:                  ele.id,
              radius:              35,
              value:               dataValue,
              maxValue:            100,
              width:               5,
              text:                function(value){return dataText != dataValue ? dataText : value;},
              colors:              [dataBgColor, dataColor]
            });
          })(eles[i]);
        }
      })();
    ');
    wp_add_inline_script('jpwa-circles', '
      jQuery(function($) {
        $(".jpwa-search-form").submit(function() {
          var $form = $(this);
          var $btns = $("button", $form);
          $btns.eq(0).hide();
          $btns.eq(1).removeClass("d-none");
        });
      });
    ');
  }

  public function loadTextDomain() {
    load_plugin_textdomain('jpwa', FALSE, basename(dirname(JPWA_PLUGIN_FILE)) . '/languages/');
  }

  public function registerCustomPost() {
    $labels = [
      'name' => __( 'Website Audits', 'jpwa' ),
      'singular_name' => __( 'Website Audit', 'jpwa' ),
    ];

    $args = [
      'label' => __( 'Website Audits', 'jpwa' ),
      'labels' => $labels,
      'description' => '',
      'public' => false,
      'publicly_queryable' => false,
      'show_ui' => true,
      'delete_with_user' => false,
      'show_in_rest' => false,
      'rest_base' => '',
      'rest_controller_class' => 'WP_REST_Posts_Controller',
      'has_archive' => false,
      'show_in_menu' => true,
      'show_in_nav_menus' => true,
      'exclude_from_search' => true,
      'capability_type' => 'post',
      'map_meta_cap' => true,
      'hierarchical' => false,
      'rewrite' => [
        'slug' => 'audits',
        'with_front' => true
      ],
      'query_var' => true,
    ];

    register_post_type('jpwa_audit', $args);
  }

  public function registerCustomFields() {
    if (!function_exists('acf_add_local_field_group')) {
      return;
    }

    $auditor = new Auditor(null, null, false);

    $fields = [
      [
        'key' => 'field_jpwa_last_modified',
        'label' => __('Last Modified', 'jpwa'),
        'name' => 'jpwa_last_modified',
        'type' => 'text'
      ]
    ];

    foreach ($auditor->getCategories() as $category) {
      $subFields = [];

      foreach ($category->getAudits() as $audit) {
        $subFields[] = [
          'key' => 'field_jpwa_audit_' . $audit->getId(),
          'label' => $audit->getDescription(),
          'name' => 'jpwa_audit_' . $audit->getId(),
          'type' => 'number'
        ];
      }

      $fields[] = [
        'key' => 'field_jpwa_category_' . $category->getId(),
        'label' => $category->getTitle(),
        'name' => 'jpwa_category_' . $category->getId(),
        'type' => 'group',
        'layout' => 'row',
        'sub_fields' => $subFields
      ];
    }

    acf_add_local_field_group(
      [
        'key' => 'group_jpwa_5c84153cb0c3e',
        'title' => 'Jon Pontet Website Audit',
        'fields' => $fields,
        'location' => [
          [
            [
              'param' => 'post_type',
              'operator' => '==',
              'value' => 'jpwa_audit',
            ],
          ],
        ],
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => true,
        'description' => '',
      ]
    );
  }

  public function tryToHandleRequest() {
    if (empty($_POST['jpwa_audit'])) {
      return;
    }

    $url = $_POST['jpwa_audit'];

    $rateLimiter = self::$rateLimiter;
    if (!$rateLimiter->canAdd()) {
      $error = __('We are already processing way too many requests. Please try again later.', 'jpwa');
      wp_redirect(esc_url_raw(add_query_arg('audit_error', $error, self::getFormPageUrl())));
      self::logError($error);
      exit;
    }

    $rateLimitKey = $rateLimiter->add();

    $auditor = new \JonPontet\WebsiteAudit\Core\Auditor($url, self::$settings);
    self::$auditor = $auditor;

    if ($auditor->hasErrors()) {
      $rateLimiter->remove($rateLimitKey);
      wp_redirect(esc_url_raw(add_query_arg('audit_error', $auditor->getErrors()[0], self::getFormPageUrl())));
      exit;
    }

    $ctx = $auditor->getContext();

    $evaluated = false;
    if ($ctx->get('shouldEvaluate')) {
      $auditor->evaluate(true);
      $evaluated = true;
    }

    $result = Result::findByDomain($ctx->get('domain'));
    if ($result === null) {
      $result = new Result();
      $auditor->evaluate(true);
      $evaluated = true;
    }

    if ($auditor->hasErrors()) {
      $rateLimiter->remove($rateLimitKey);
      $error = __('We couldn\'t process your request. Please try again later.', 'jpwa');
      wp_redirect(esc_url_raw(add_query_arg('audit_error', $error, self::getFormPageUrl())));
      self::logError($error);
      exit;
    }

    $result->setDomain($ctx->get('domain'));
    $result->setLastModified($ctx->get('headers')['last-modified']);
    if ($evaluated) {
      $result->setAuditor($auditor);
    }
    $result->save();

    self::$result = $result;

    $rateLimiter->remove($rateLimitKey);
    wp_redirect(self::getCurrentResultPageUrl());
    exit;
  }

  public function tryToHandlePermalink() {
    if (!isset($_GET['id'])) {
      return;
    }

    $id = $_GET['id'];

    if (!get_post_status($id)) {
      return;
    }

    $result = new Result($id);
    self::$result = $result;
  }

  public function tryToHandleEmailSend() {
    if (!isset($_POST['jpwa_email'])) {
      return;
    }

    $email = $_POST['jpwa_email'];

    $to = $email;
    $subject = 'Your Website Audit Report';
    $body = self::$settings->get('email');

    $result = self::$result;
//    error_log(print_r($email,true));
    foreach ($result->getCategories() as $category) {
      $body = str_replace('{{category_'.$category->getId().'_title}}', $category->getTitle(), $body);
      $body = str_replace('{{category_'.$category->getId().'_score_formatted}}', $category->getScoreFormatted(), $body);

      foreach ($category->getAudits() as $audit) {
        $body = str_replace('{{audit_'.$audit->getId().'_description}}', $audit->getDescription(), $body);
        $body = str_replace('{{audit_'.$audit->getId().'_tick}}', $audit->getScore() < 1 ? 'X' : '✓', $body);
      }
    }

    $headers = ['Content-Type: text/html; charset=UTF-8'];

    $emailOk = wp_mail($to, $subject, $body, $headers);

    /*if (isset($_POST['jpwa_consent'])) {
      $region = self::$settings->get('mailchimp_region');
      $user = self::$settings->get('mailchimp_user');
      $listId = self::$settings->get('mailchimp_list_id');

      if (!empty($region) && !empty($user) && !empty($listId)) {
        wp_remote_post('http://etalented.' . $region . '.list-manage.com/subscribe/post',[
          'headers' => [
            'Content-Type: application/x-www-form-urlencoded'
          ],
          'body' => http_build_query([
            'u' => $user,
            'id' => $listId,
            'EMAIL' => $to,
          ])
        ]);
      }
    }*/

    if (!$emailOk) {
    wp_redirect($this->getEmailFailurePageUrl());
      exit;
    }
      wp_redirect($this->getEmailSuccessPageUrl());

    exit;
  }
  
  public static function logError($error) {
    $time = date_i18n( 'm-d-Y @ H:i:s' );
    $entry = "{$time} - {$error}";

    $resource = @fopen( self::$logFile, 'a' ); // @codingStandardsIgnoreLine.
    
    if ( $resource ) {
        $result = fwrite( $resource, $entry . PHP_EOL ); // @codingStandardsIgnoreLine.
    }
  }

  public static function getFormPageUrl() {
    return get_permalink(self::$settings->get('page_form_id'));
  }

  public static function getResultPageUrl() {
    return get_permalink(self::$settings->get('page_result_id'));
  }

  public static function getEmailSuccessPageUrl() {
    return get_permalink(self::$settings->get('page_email_success_id'));
  }

  public static function getEmailFailurePageUrl() {
    return get_permalink(self::$settings->get('page_email_failure_id'));
  }

  public static function getCurrentResultPageUrl(): string {
    return add_query_arg('id', self::$result->getId(), self::getResultPageUrl());
  }
  
  public static function poeditTranslationStrings() {
    $text = [
      __("category_title_accessibility",'jpwa'),
      __("category_subtitle_accessibility",'jpwa'),
      __("category_description_accessibility",'jpwa'),
      __("category_source_accessibility",'jpwa'),
      __("category_title_conversion",'jpwa'),
      __("category_subtitle_conversion",'jpwa'),
      __("category_description_conversion",'jpwa'),
      __("category_source_conversion",'jpwa'),
      __("category_title_mobile-friendly",'jpwa'),
      __("category_subtitle_mobile-friendly",'jpwa'),
      __("category_description_mobile-friendly",'jpwa'),
      __("category_source_mobile-friendly",'jpwa'),
      __("category_title_seo",'jpwa'),
      __("category_subtitle_seo",'jpwa'),
      __("category_description_seo",'jpwa'),
      __("category_source_seo",'jpwa'),
      __("category_title_social-media",'jpwa'),
      __("category_subtitle_social-media",'jpwa'),
      __("category_description_social-media",'jpwa'),
      __("category_source_social-media",'jpwa'),
      __("category_title_speed",'jpwa'),
      __("category_subtitle_speed",'jpwa'),
      __("category_description_speed",'jpwa'),
      __("category_source_speed",'jpwa'),
      __("category_title_wordpress-security",'jpwa'),
      __("category_subtitle_wordpress-security",'jpwa'),
      __("category_description_wordpress-security",'jpwa'),
      __("category_source_wordpress-security",'jpwa'),
      __("audit_description_accessibility",'jpwa'),
      __("audit_description_disabled-pingbacks-and-trackbacks",'jpwa'),
      __("audit_description_disabled-rest-user-scan",'jpwa'),
      __("audit_description_external-links",'jpwa'),
      __("audit_description_has-ssl",'jpwa'),
      __("audit_description_internal-links",'jpwa'),
      __("audit_description_meta-description",'jpwa'),
      __("audit_description_meta-facebook",'jpwa'),
      __("audit_description_meta-twitter",'jpwa'),
      __("audit_description_mobile-friendliness",'jpwa'),
      __("audit_description_presence-contact-form",'jpwa'),
      __("audit_description_presence-google-analytics",'jpwa'),
      __("audit_description_robots-txt",'jpwa'),
      __("audit_description_sitemap-xml",'jpwa'),
      __("audit_description_speed",'jpwa'),
      __("audit_description_tag-h1",'jpwa'),
      __("audit_description_tag-title",'jpwa'),
      __("audit_description_tag-title-length",'jpwa'),
      __("audit_description_unauthorised-load-scripts",'jpwa'),
      __("audit_description_unauthorised-load-styles",'jpwa'),
      __("audit_description_wp-login-404",'jpwa'),
    ];
  }

}