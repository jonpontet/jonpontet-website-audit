<?php
namespace JonPontet\WebsiteAudit\Category;

use \JonPontet\WebsiteAudit\Core\Category;

class Speed extends Category {

  public function __construct(array $audits = []) {
    parent::__construct('speed', $audits);
  }

}
