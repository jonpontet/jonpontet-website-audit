<?php
namespace JonPontet\WebsiteAudit\Audit;

use \JonPontet\WebsiteAudit\Core\Audit;
use \JonPontet\WebsiteAudit\Core\AuditContext;

class Speed extends Audit {

  public function __construct(AuditContext $context) {
    parent::__construct('speed', $context);
  }

  public function evaluate(): void {
    $response = $this->context->get('requests')['pageSpeed'];

    $body = $response->response;
    $result = json_decode($body, true);

    if (isset($result['error']['errors']) && count($result['error']['errors']) > 0) {
      $this->addError('Lighthouse error' . json_encode($result['error']['errors']));
    }

    $this->context->set('pageSpeedResult', $result);

    if (isset($result['lighthouseResult']['categories']['performance']['score'])) {
      $this->setScore($result['lighthouseResult']['categories']['performance']['score']);
    }
  }

}
