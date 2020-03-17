<?php
namespace JonPontet\WebsiteAudit\Category;

use \JonPontet\WebsiteAudit\Core\Category;

class Conversion extends Category {

  public function __construct(array $audits = []) {
    parent::__construct('conversion', $audits);
  }

}
