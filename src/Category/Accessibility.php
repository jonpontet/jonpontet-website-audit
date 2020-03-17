<?php
namespace JonPontet\WebsiteAudit\Category;

use \JonPontet\WebsiteAudit\Core\Category;

class Accessibility extends Category {

  public function __construct(array $audits = []) {
    parent::__construct('accessibility', $audits);
  }

}
