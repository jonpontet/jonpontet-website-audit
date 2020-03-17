<?php
namespace JonPontet\WebsiteAudit\Audit;

use \JonPontet\WebsiteAudit\Core\Audit;
use \JonPontet\WebsiteAudit\Core\AuditContext;

class RobotsTxt extends Audit {

  public function __construct(AuditContext $context) {
    parent::__construct('robots-txt', $context);
  }

  public function evaluate(): void {
    $response = $this->context->get('requests')['robots.txt'];
    $code = $response->code;

    if ($code === 200) {
      $this->setScore(1);
    }
  }

}
