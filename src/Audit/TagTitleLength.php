<?php
namespace JonPontet\WebsiteAudit\Audit;

use \JonPontet\WebsiteAudit\Core\Audit;
use \JonPontet\WebsiteAudit\Core\AuditContext;

class TagTitleLength extends Audit {

  public function __construct(AuditContext $context) {
    parent::__construct('tag-title-length', $context);
  }

  public function evaluate(): void {
    $crawler = $this->context->get('crawler');

    try {
      $eleTitle = $crawler->filter('head > title')->first();

      $text = $eleTitle->text();
      $length = strlen($text);

      if ($length >= 10 && $length <= 60) {
        $this->setScore(1);
      }
    } catch (\InvalidArgumentException $e) {}
  }

}
