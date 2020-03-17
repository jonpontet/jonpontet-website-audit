<?php
namespace JonPontet\WebsiteAudit\Category;

use \JonPontet\WebsiteAudit\Core\Category;

class SocialMedia extends Category {

  public function __construct(array $audits = []) {
    parent::__construct('social-media', $audits);
  }

}
