<?php
namespace JonPontet\WebsiteAudit\Audit;

use \JonPontet\WebsiteAudit\Core\Audit;
use \JonPontet\WebsiteAudit\Core\AuditContext;

class PresenceGoogleAnalytics extends Audit {

  public function __construct(AuditContext $context) {
    parent::__construct('presence-google-analytics', $context);
  }

  public function evaluate(): void {
    $crawler = $this->context->get('crawler');

    $scriptContents = $crawler->filter('script')->each(function ($node) {
      return $node->text();
    });

    $hasGa = array_filter($scriptContents, function ($str) {
      // https://developers.google.com/analytics/devguides/collection/gajs/
      if (stripos($str, 'google-analytics.com/ga.js') !== false) {
        return true;
      }

      // https://developers.google.com/analytics/devguides/collection/analyticsjs/
      if (stripos($str, 'google-analytics.com/analytics.js') !== false) {
        return true;
      }

      // https://developers.google.com/gtagjs/devguide/snippet
      if (stripos($str, 'googletagmanager.com/gtag/js') !== false) {
        return true;
       }

      // https://developers.google.com/tag-manager/quickstart
      if (stripos($str, 'googletagmanager.com/gtm.js') !== false) {
        preg_match('/[\'"]GTM-[^\'"]+/', $str, $matches);

        if (count($matches) > 1) {
          $gtmId = $matches[1];
          $response  = wp_remote_get('https://www.googletagmanager.com/gtm.js?id=' . $gtmId);
          $body = wp_remote_retrieve_body($response);
          if (stripos($body, 'UA-') !== false) {
            return true;
          }
        }
      }

      return false;
    });

    if (!empty($hasGa)) {
      $this->setScore(1);
    }
  }

}
