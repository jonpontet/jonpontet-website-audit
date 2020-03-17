<?php
namespace JonPontet\WebsiteAudit\Audit;

use \JonPontet\WebsiteAudit\Core\Audit;
use \JonPontet\WebsiteAudit\Core\AuditContext;

class UnauthorisedLoadStyles extends Audit {

  public function __construct(AuditContext $context) {
    parent::__construct('unauthorised-load-styles', $context);
  }

  public function evaluate(): void {
    $response = $this->context->get('requests')['load-styles.php'];
    $code = $response->code;

    if ($code !== 200) {
      $this->setScore(1);
    }
  }

}
