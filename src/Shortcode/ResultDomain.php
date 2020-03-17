<?php
namespace JonPontet\WebsiteAudit\Shortcode;

use \JonPontet\WebsiteAudit\Core\Shortcode;
use \JonPontet\WebsiteAudit\WebsiteAuditPlugin;

class ResultDomain extends Shortcode {

  public function __construct() {
    parent::__construct('jpwa_result_domain');
  }

  public function render($atts): string {
    $result = WebsiteAuditPlugin::$result;

    if (!$result) {
      return '';
    }

    return $result->getDomain();
  }

}
