<?php
namespace JonPontet\WebsiteAudit\Audit;

use \JonPontet\WebsiteAudit\Core\Audit;
use \JonPontet\WebsiteAudit\Core\AuditContext;

class UnauthorisedLoadScripts extends Audit {

  public function __construct(AuditContext $context) {
    parent::__construct('unauthorised-load-scripts', $context);
  }

  public function evaluate(): void {
    $response = $this->context->get('requests')['load-scripts.php'];
    $code = $response->code;

    if ($code !== 200) {
      $this->setScore(1);
    }
  }

}
