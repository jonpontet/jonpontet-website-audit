<?php
namespace JonPontet\WebsiteAudit\Audit;

use \JonPontet\WebsiteAudit\Core\Audit;
use \JonPontet\WebsiteAudit\Core\AuditContext;

class InternalLinks extends Audit {

  public function __construct(AuditContext $context) {
    parent::__construct('internal-links', $context);
  }

  public function evaluate(): void {
    $crawler = $this->context->get('crawler');
    $domain = $this->context->get('domain');
    $domainWithoutWww = str_replace('www.', '', $domain);

    $words = $crawler->filterXPath('//body//text()[not(ancestor::script and ancestor::style)]')->extract('_text');
    $words = array_map('trim', $words);
    $words = array_filter($words, 'strlen');
    $words = implode(' ', $words);
    $words = preg_replace('/\s+/', ' ', $words);
    $words = explode(' ', $words);
    $wordsCount = count($words);

    $links = $crawler->filter('body a')->each(function ($node, $i) use ($domain) {
      $href = $node->attr('href');

      $urlParts = parse_url($href);

      if ($urlParts !== false) {
        if (empty($urlParts['host'])) {
          $urlParts['host'] = $domain;
        }

        $urlParts['domain'] = str_replace('www.', '', $urlParts['host']);
      }

      if (isset($urlParts['domain'])) {
        return $urlParts;
      }

      return false;
    });

    $links = array_filter($links, function ($link) {
      return $link !== false;
    });

    $internalLinks = array_filter($links, function ($link) use ($domainWithoutWww) {
      return $link['domain'] === $domainWithoutWww;
    });
    $internalLinksCount = count($internalLinks);

    // - internal links
    $min = 4 + floor($wordsCount / 75);

    if ($internalLinksCount >= $min) {
      $this->setScore(1);
    }
  }

}
