<?php
namespace JonPontet\WebsiteAudit\Audit;

use \JonPontet\WebsiteAudit\Core\Audit;
use \JonPontet\WebsiteAudit\Core\AuditContext;

class PresenceContactForm extends Audit {

  public function __construct(AuditContext $context) {
    parent::__construct('presence-contact-form', $context);
  }

  public function evaluate(): void {
    $crawler = $this->context->get('crawler');

    try {
      $eleInput = $crawler->filter('body form input[type="text"], body form input[type="email"]')->first();

      if (count($eleInput) > 0) {
        $this->setScore(1);
      }
    } catch (\InvalidArgumentException $e) {}

  }

}
