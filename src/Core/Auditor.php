<?php
namespace JonPontet\WebsiteAudit\Core;

use JonPontet\WebsiteAudit\Category\Accessibility as CategoryAccessibility;
use JonPontet\WebsiteAudit\Category\Conversion as CategoryConversion;
use JonPontet\WebsiteAudit\Category\MobileFriendly as CategoryMobileFriendly;
use JonPontet\WebsiteAudit\Category\SEO as CategorySEO;
use JonPontet\WebsiteAudit\Category\SocialMedia as CategorySocialMedia;
use JonPontet\WebsiteAudit\Category\Speed as CategorySpeed;
use JonPontet\WebsiteAudit\Category\WordPressSecurity as CategoryWordPressSecurity;

use JonPontet\WebsiteAudit\Audit\Accessibility as AuditAccessibility;
use JonPontet\WebsiteAudit\Audit\DisabledPingbacksAndTrackbacks as AuditDisabledPingbacksAndTrackbacks;
use JonPontet\WebsiteAudit\Audit\DisabledRESTUserScan as AuditDisabledRESTUserScan;
use JonPontet\WebsiteAudit\Audit\DisabledTrackbacks as AuditDisabledTrackbacks;
use JonPontet\WebsiteAudit\Audit\ExternalLinks as AuditExternalLinks;
use JonPontet\WebsiteAudit\Audit\HasSSL as AuditHasSSL;
use JonPontet\WebsiteAudit\Audit\InternalLinks as AuditInternalLinks;
use JonPontet\WebsiteAudit\Audit\MetaDescription as AuditMetaDescription;
use JonPontet\WebsiteAudit\Audit\MetaFacebook as AuditMetaFacebook;
use JonPontet\WebsiteAudit\Audit\MetaTwitter as AuditMetaTwitter;
use JonPontet\WebsiteAudit\Audit\MobileFriendliness as AuditMobileFriendliness;
use JonPontet\WebsiteAudit\Audit\PresenceContactForm as AuditPresenceContactForm;
use JonPontet\WebsiteAudit\Audit\PresenceGoogleAnalytics as AuditPresenceGoogleAnalytics;
use JonPontet\WebsiteAudit\Audit\RobotsTxt as AuditRobotsTxt;
use JonPontet\WebsiteAudit\Audit\SitemapXML as AuditSitemapXML;
use JonPontet\WebsiteAudit\Audit\Speed as AuditSpeed;
use JonPontet\WebsiteAudit\Audit\TagH1 as AuditTagH1;
use JonPontet\WebsiteAudit\Audit\TagTitle as AuditTagTitle;
use JonPontet\WebsiteAudit\Audit\TagTitleLength as AuditTagTitleLength;
use JonPontet\WebsiteAudit\Audit\UnauthorisedLoadScripts as AuditUnauthorisedLoadScripts;
use JonPontet\WebsiteAudit\Audit\UnauthorisedLoadStyles as AuditUnauthorisedLoadStyles;
use JonPontet\WebsiteAudit\Audit\WpLogin404 as AuditWpLogin404;

use JonPontet\WebsiteAudit\Core\Settings;

use JonPontet\WebsiteAudit\WebsiteAuditPlugin;

class Auditor {

  private $context;
  private $categories = [];
  private $errors = [];

  public function __construct(?string $url, ?Settings $settings, bool $init = true) {
    $this->context = new AuditContext([
      'settings' => $settings
    ]);

    $this->initAudits();

    if ($init) {
      $this->init($url);
    }
  }
  
  protected function addError($error): void {
    $this->errors[] = $error;
    
    WebsiteAuditPlugin::logError($error);
  }

  public function init($url) {
    $urlParts = parse_url($url);
    $domain = (isset($urlParts['host'])) ? $urlParts['host'] : $urlParts['path'];

    extract($this->determineProtocol($domain));
    if (empty($protocol)) {
      $this->addError(__('Website is unreachable.', 'jpwa'));
      
      return;
    }

    $url = $protocol . '://' . $domain;

    $headers = wp_remote_retrieve_headers($response);

    $existingResult = Result::findByDomain($domain);
    if ($existingResult !== null && !empty($existingResult->getLastModified()) && !empty($headers['last-modified'])) {
      $time1 = strtotime($existingResult->getLastModified());
      $time2 = strtotime($headers['last-modified']);

      $this->context->set('shouldEvaluate', $time1 < $time2);
    }

    $body = wp_remote_retrieve_body($response);
    $crawler = new \Symfony\Component\DomCrawler\Crawler($body);

    $wordpress = false;
    try {
      $meta = $crawler->filter('head > meta[name="generator"]')->first();

      $text = $meta->attr('content');
      if (stripos($text, 'wordpress') !== FALSE) {
        $wordpress = true;
      }
    } catch (\InvalidArgumentException $e) {}

    if (!$wordpress) {
      $this->removeCategory('wordpress-security');
    }

    $this->context->merge(compact('domain', 'protocol', 'url', 'response', 'headers', 'crawler', 'existingResult', 'wordpress'));

    // Batch all requests
    $mc = \JMathai\PhpMultiCurl\MultiCurl::getInstance();
    $requests = [
      'wp-login.php'       => ($wordpress) ? $mc->addUrl($url . '/wp-login.php') : null,
      'load-styles.php'    => ($wordpress) ? $mc->addUrl($url . '/wp-admin/load-styles.php') : null,
      'load-scripts.php'   => ($wordpress) ? $mc->addUrl($url . '/wp-admin/load-scripts.php') : null,
      'restUsers'          => ($wordpress) ? $mc->addUrl($url . '/wp-json/wp/v2/users') : null,
      'restPosts'          => ($wordpress) ? $mc->addUrl($url . '/wp-json/wp/v2/posts') : null,

      'robots.txt'         => $mc->addUrl($url . '/robots.txt', [CURLOPT_FOLLOWLOCATION => true]),
      'sitemap.xml'        => $mc->addUrl($url . '/sitemap.xml', [CURLOPT_FOLLOWLOCATION => true]),

      'mobileFriendlyTest' => $mc->addUrl(add_query_arg(
        [
          'key' => $this->context->get('settings')->get('urltestingtools_api_key')
        ],
        'https://searchconsole.googleapis.com/v1/urlTestingTools/mobileFriendlyTest:run'
      ), [
        CURLOPT_TIMEOUT => 300,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode(['url' => $this->context->get('url')]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json']
      ]),
      'pageSpeed'          => $mc->addUrl(add_query_arg(
        [
        'key' => $this->context->get('settings')->get('pagespeed_api_key'),
        'url' => $this->context->get('url'),
        'category' => 'performance',
        'fields' => 'lighthouseResult(categories(accessibility/score,performance/score),runtimeError)'
        ],
        'https://www.googleapis.com/pagespeedonline/v5/runPagespeed'
      ) . '&category=accessibility', [CURLOPT_TIMEOUT => 300])
    ];

    $this->context->set('requests', $requests);
  }

  private function initAudits() {
    $ctx = $this->context;

    $this->categories = [
      new CategorySpeed([
        new AuditSpeed($ctx)
      ]),

      new CategoryConversion([
        new AuditPresenceContactForm($ctx),
        new AuditPresenceGoogleAnalytics($ctx)
      ]),

      new CategorySEO([
        new AuditRobotsTxt($ctx),
        new AuditSitemapXml($ctx),
        new AuditMetaDescription($ctx),
        new AuditTagTitle($ctx),
        new AuditTagTitleLength($ctx),
        new AuditTagH1($ctx),
        new AuditInternalLinks($ctx),
        new AuditExternalLinks($ctx)
      ]),

      new CategoryMobileFriendly([
        new AuditMobileFriendliness($ctx)
      ]),

      new CategorySocialMedia([
        new AuditMetaFacebook($ctx),
        new AuditMetaTwitter($ctx)
      ]),

      new CategoryWordPressSecurity([
        new AuditHasSSL($ctx),
        new AuditWpLogin404($ctx),
        new AuditDisabledRESTUserScan($ctx),
        new AuditUnauthorisedLoadScripts($ctx),
        new AuditUnauthorisedLoadStyles($ctx),
        new AuditDisabledPingbacksAndTrackbacks($ctx)
      ]),

      new CategoryAccessibility([
        new AuditAccessibility($ctx)
      ]),
    ];
  }

  public function removeCategory($id) {
    foreach ($this->categories as $k => $category) {
      if ($category->getId() === $id) {
        unset($this->categories[$k]);
      }
    }
  }

  private function determineProtocol($domain): array {
    $response = wp_remote_get('https://' . $domain);

    if (is_wp_error($response)) {
      $response = wp_remote_get('http://' . $domain);

      if (is_wp_error($response)) {
        return ['protocol' => '', 'response' => ''];
      }

      return ['protocol' => 'http', 'response' => $response];
    }

    return ['protocol' => 'https', 'response' => $response];
  }

  private function evaluateAudit($audit): bool {
    try {
      $audit->evaluate();
    } catch (\Exception $e) {
      $this->addError($e);
      return false;
    }

    if ($audit->hasErrors()) {
      $this->errors = array_merge($this->errors, $audit->getErrors());
      return false;
    }

    return true;
  }

  public function getCategories(): array {
    return $this->categories;
  }

  public function getContext(): AuditContext {
    return $this->context;
  }

  public function getErrors(): array {
    return $this->errors;
  }

  public function hasErrors(): bool {
    return count($this->errors) > 0;
  }

  public function evaluate($failFast = false): bool {
    $timeLimit = ini_get('max_execution_time');
    set_time_limit(0);

    foreach ($this->categories as $category) {
      foreach ($category->getAudits() as $audit) {
        $success = $this->evaluateAudit($audit);

        if (!$success && $failFast) {
          return false;
        }
      }
    }

    set_time_limit($timeLimit);

    return $this->hasErrors();
  }

}