<?php
namespace JonPontet\WebsiteAudit\Category;

use \JonPontet\WebsiteAudit\Core\Category;

class WordPressSecurity extends Category {

  public function __construct(array $audits = []) {
    parent::__construct('wordpress-security', $audits);
  }

}
