<?php
namespace JonPontet\WebsiteAudit\Audit;

use \JonPontet\WebsiteAudit\Core\Audit;
use \JonPontet\WebsiteAudit\Core\AuditContext;

class HasSSL extends Audit {

  public function __construct(AuditContext $context) {
    parent::__construct('has-ssl', $context);
  }

  public function evaluate(): void {
    $isHttps = $this->context->get('protocol') === 'https';

    if ($isHttps) {
      $this->setScore(1);
    }
  }

}