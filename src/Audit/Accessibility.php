<?php
namespace JonPontet\WebsiteAudit\Audit;

use \JonPontet\WebsiteAudit\Core\Audit;
use \JonPontet\WebsiteAudit\Core\AuditContext;

class Accessibility extends Audit {

  public function __construct(AuditContext $context) {
    parent::__construct('accessibility', $context);
  }

  public function evaluate(): void {
    $result = $this->context->get('pageSpeedResult');

    if (isset($result['lighthouseResult']['categories']['accessibility']['score'])) {
      $this->setScore($result['lighthouseResult']['categories']['accessibility']['score']);
    }
  }

}
