<?php
namespace JonPontet\WebsiteAudit\Category;

use \JonPontet\WebsiteAudit\Core\Category;

class MobileFriendly extends Category {

  public function __construct(array $audits = []) {
    parent::__construct('mobile-friendly', $audits);
  }

  public function getScoreFormatted(): string {
    return ((int) $this->getScore() === 1) ? 'YES' : 'NO';
  }

}
