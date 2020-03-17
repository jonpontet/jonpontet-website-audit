<?php
namespace JonPontet\WebsiteAudit\Audit;

use \JonPontet\WebsiteAudit\Core\Audit;
use \JonPontet\WebsiteAudit\Core\AuditContext;

class TagH1 extends Audit {

  public function __construct(AuditContext $context) {
    parent::__construct('tag-h1', $context);
  }

  public function evaluate(): void {
    $crawler = $this->context->get('crawler');

    try {
      $eleH1 = $crawler->filter('body h1')->first();

      $text = $eleH1->text();
      $length = strlen($text);

      if ($length > 0) {
        $this->setScore(1);
      }
    } catch (\InvalidArgumentException $e) {}
  }

}
