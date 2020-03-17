<?php
namespace JonPontet\WebsiteAudit\Audit;

use \JonPontet\WebsiteAudit\Core\Audit;
use \JonPontet\WebsiteAudit\Core\AuditContext;

class MetaTwitter extends Audit {

  public function __construct(AuditContext $context) {
    parent::__construct('meta-twitter', $context);
  }

  public function evaluate(): void {
    $crawler = $this->context->get('crawler');

    $metas = ['twitter:title', 'twitter:site', 'twitter:image', 'twitter:description'];
    $ok = [];

    foreach ($metas as $meta) {
      try {
        $eleMeta = $crawler->filter('head > meta[property="' . $meta . '"], head > meta[name="' . $meta . '"]')->first();
        $text = $eleMeta->attr('content');
        $length = strlen($text);

        if ($length > 0) {
          $ok[$meta] = true;
        }
      } catch (\InvalidArgumentException $e) {}
    }

    if (count($metas) === count($ok)) {
      $this->setScore(1);
    }
  }

}
