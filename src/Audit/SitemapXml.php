<?php
namespace JonPontet\WebsiteAudit\Audit;

use \JonPontet\WebsiteAudit\Core\Audit;
use \JonPontet\WebsiteAudit\Core\AuditContext;

class SitemapXml extends Audit {

  public function __construct(AuditContext $context) {
    parent::__construct('sitemap-xml', $context);
  }

  public function evaluate(): void {
    $response = $this->context->get('requests')['sitemap.xml'];
    $code = $response->code;

    if ($code === 200) {
      $this->setScore(1);
    }
  }

}
