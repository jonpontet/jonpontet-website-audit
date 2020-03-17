<?php
namespace JonPontet\WebsiteAudit\Audit;

use \JonPontet\WebsiteAudit\Core\Audit;
use \JonPontet\WebsiteAudit\Core\AuditContext;

class DisabledPingbacksAndTrackbacks extends Audit {

  public function __construct(AuditContext $context) {
    parent::__construct('disabled-pingbacks-and-trackbacks', $context);
  }

  public function evaluate(): void {
    $response = $this->context->get('requests')['restPosts'];

    $body = $response->response;
    $data = json_decode($body, true);

    if (empty($data) || !is_array($data) || count($data) < 1) {
      // No data available
    } elseif ($data[0]['ping_status'] === 'closed' && discover_pingback_server_uri($data[0]['link']) === false) {
      $this->setScore(1);
    }
  }

}
