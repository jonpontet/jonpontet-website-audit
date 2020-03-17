<?php
namespace JonPontet\WebsiteAudit\Audit;

use \JonPontet\WebsiteAudit\Core\Audit;
use \JonPontet\WebsiteAudit\Core\AuditContext;

class DisabledRESTUserScan extends Audit {

  public function __construct(AuditContext $context) {
    parent::__construct('disabled-rest-user-scan', $context);
  }

  public function evaluate(): void {
    $response = $this->context->get('requests')['restUsers'];

    $body = $response->response;
    $data = json_decode($body, true);

    if (!empty($data) && is_array($data) && count($data) > 0 && !empty($data[0]['id']) && !empty($data[0]['name'])) {
      $this->setScore(0);
    } else {
      $this->setScore(1);
    }
  }

}
