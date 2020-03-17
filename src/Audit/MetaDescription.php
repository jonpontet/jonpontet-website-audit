<?php
namespace JonPontet\WebsiteAudit\Audit;

use \JonPontet\WebsiteAudit\Core\Audit;
use \JonPontet\WebsiteAudit\Core\AuditContext;

class MetaDescription extends Audit {

  public function __construct(AuditContext $context) {
    parent::__construct('meta-description', $context);
  }

  public function evaluate(): void {
    $crawler = $this->context->get('crawler');

    try {
      $eleMetaDescription = $crawler->filter('head > meta[name="description"]')->first();
      $text = $eleMetaDescription->attr('content');
      $length = strlen($text);

      if ($length > 0) {
        $this->setScore(1);
      }
    } catch (\InvalidArgumentException $e) {}
  }

}
