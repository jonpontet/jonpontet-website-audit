<?php
namespace JonPontet\WebsiteAudit\Core;

class Settings {

  private $settings_api;

  public function __construct() {
    $this->settings_api = new \WeDevs_Settings_API;
    add_action('admin_init', [$this, 'admin_init']);
    add_action('admin_menu', [$this, 'admin_menu']);
  }

  public function admin_init() {
    $this->settings_api->set_sections($this->get_settings_sections());
    $this->settings_api->set_fields($this->get_settings_fields());
    $this->settings_api->admin_init();
  }

  public function admin_menu() {
    add_options_page('Jon Pontet Website Audit', 'Jon Pontet Website Audit', 'delete_posts', 'jpwa_settings', [$this, 'plugin_page']);
  }

  public function get_settings_sections() {
    $sections = [
      [
        'id' => 'jpwa_settings',
        'title' => __('Settings', 'jpwa'),
      ]
    ];

    return $sections;
  }

  public function get_settings_fields() {
    $settings_fields = [
      'jpwa_settings' => [
        [
          'name' => 'ssl_verify',
          'label' => __('SSL verify', 'jpwa'),
          'type' => 'checkbox',
          'default' => false
        ],
        [
          'name' => 'concurrent_requests',
          'label' => __('Concurrent requests', 'jpwa'),
          'type' => 'text',
          'default' => 5
        ],
        [
          'name' => 'request_expiry_time',
          'label' => __('Request expiry time in seconds', 'jpwa'),
          'type' => 'text',
          'default' => MINUTE_IN_SECONDS
        ],
        [
          'name' => 'mailchimp_region',
          'label' => __('MailChimp Region', 'jpwa'),
          'type' => 'text'
        ],
        [
          'name' => 'mailchimp_user',
          'label' => __('MailChimp User', 'jpwa'),
          'type' => 'text'
        ],
        [
          'name' => 'mailchimp_list_id',
          'label' => __('MailChimp List ID', 'jpwa'),
          'type' => 'text'
        ],
        [
          'name' => 'pagespeed_api_key',
          'label' => __('PageSpeed API Key', 'jpwa'),
          'type' => 'text'
        ],
        [
          'name' => 'urltestingtools_api_key',
          'label' => __('URL Testing Tools API Key', 'jpwa'),
          'type' => 'text'
        ],
        [
          'name' => 'email',
          'label' => __('Email template', 'jpwa'),
          'type' => 'textarea'
        ],
        [
          'name' => 'page_form_id',
          'label' => __('ID of the page that contains the inital state', 'jpwa'),
          'type' => 'text'
        ],
        [
          'name' => 'page_result_id',
          'label' => __('ID of the page that contains the result state', 'jpwa'),
          'type' => 'text'
        ],
        [
          'name' => 'page_email_success_id',
          'label' => __('ID of the page that contains the email success state', 'jpwa'),
          'type' => 'text'
        ],
        [
          'name' => 'page_email_failure_id',
          'label' => __('ID of the page that contains the email failure state', 'jpwa'),
          'type' => 'text'
        ]
      ]
    ];

    return $settings_fields;
  }

  public function plugin_page() {
    echo '<div class="wrap">';
    $this->settings_api->show_navigation();
    $this->settings_api->show_forms();
    echo '</div>';
  }

  function get($option, $default = '') {
    $options = get_option('jpwa_settings');

    if (isset( $options[$option])) {
      return $options[$option];
    }

    return $default;
  }

}
