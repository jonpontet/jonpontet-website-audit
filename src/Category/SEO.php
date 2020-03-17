<?php
namespace JonPontet\WebsiteAudit\Category;

use \JonPontet\WebsiteAudit\Core\Category;

class SEO extends Category {

  public function __construct(array $audits = []) {
    parent::__construct('seo', $audits);
  }

}
