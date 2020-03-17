<?php
namespace JonPontet\WebsiteAudit\Audit;

use \JonPontet\WebsiteAudit\Core\Audit;
use \JonPontet\WebsiteAudit\Core\AuditContext;

class MobileFriendliness extends Audit {

  public function __construct(AuditContext $context) {
    parent::__construct('mobile-friendliness', $context);
  }

  public function evaluate(): void {
    $response = $this->context->get('requests')['mobileFriendlyTest'];

    $body = $response->response;
    $result = json_decode($body, true);

    $isTestComplete = isset($result['testStatus'])  && isset($result['testStatus']['status']) && $result['testStatus']['status'] === 'COMPLETE';
    $isSuccessful = $isTestComplete && isset($result['mobileFriendliness']) && $result['mobileFriendliness'] !== 'MOBILE_FRIENDLY_TEST_RESULT_UNSPECIFIED';

    /*
      Values returned by Mobile Friendly Test

      - MOBILE_FRIENDLY
      - NOT_MOBILE_FRIENDLY
      - MOBILE_FRIENDLY_TEST_RESULT_UNSPECIFIED
    */

    if (isset($result['mobileFriendliness']) && $result['mobileFriendliness'] === 'MOBILE_FRIENDLY') {
      $this->setScore(1);
    }
  }

}
