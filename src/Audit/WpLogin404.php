<?php
namespace JonPontet\WebsiteAudit\Audit;

use \JonPontet\WebsiteAudit\Core\Audit;
use \JonPontet\WebsiteAudit\Core\AuditContext;

class WpLogin404 extends Audit {

  public function __construct(AuditContext $context) {
    parent::__construct('wp-login-404', $context);
  }

  public function evaluate(): void {
    $response = $this->context->get('requests')['wp-login.php'];
    $code = $response->code;

    if ($code === 404) {
      $this->setScore(1);
    }
  }

}