<?php
namespace JonPontet\WebsiteAudit\Core;

use \JonPontet\WebsiteAudit\Core\Auditor as Auditor;


class Result {

  private $id;
  private $domain;
  private $lastModified;

  public function __construct(int $id = 0, bool $autoload = true) {
    $this->auditor = new Auditor(null, null, false);

    $this->id = $id;

    if ($this->id !== 0 && $autoload && get_post_status($id)) {
      $this->load();
    }
  }

  public function setAuditor($auditor) {
    $this->auditor = $auditor;
  }

  protected function load() {
    $this->domain = get_the_title($this->id);
    $this->lastModified = get_field('jpwa_last_modified', $this->id);

    foreach ($this->auditor->getCategories() as $k => $category) {
      $group = get_field('jpwa_category_' . $category->getId(), $this->id);

      if ($group) {
        foreach ($category->getAudits() as $audit) {
          $audit->setScore($group['jpwa_audit_' . $audit->getId()]);
        }
      } else {
        $this->auditor->removeCategory($category->getId());
      }
    }
  }

  public function save() {
    if ($this->id === 0) {
      $this->id = wp_insert_post([
        'post_title' => $this->domain,
        'post_status' => 'publish',
        'post_type' => 'jpwa_audit',
      ]);
    }

    foreach ($this->auditor->getCategories() as $category) {
      $group = [];

      foreach ($category->getAudits() as $audit) {
        $group['jpwa_audit_' . $audit->getId()] = $audit->getScore();
      }

      update_field('jpwa_category_' . $category->getId(), $group, $this->id);
    }

    wp_update_post([ 'ID' => $this->id ]);
  }

  public function getId() {
    return $this->id;
  }

  public function getDomain() {
    return $this->domain;
  }

  public function getLastModified() {
    return $this->lastModified;
  }

  public function setDomain($domain) {
    $this->domain = $domain;
  }

  public function setLastModified($lastModified) {
    $this->lastModified = $lastModified;
  }

  public function getCategories() {
    return $this->auditor->getCategories();
  }

  public static function findByDomain($domain) {
    global $wpdb;

    $id = $wpdb->get_col($wpdb->prepare('SELECT ID from ' . $wpdb->posts . ' WHERE post_title = %s LIMIT 1', $domain));

    if (count($id) > 0) {
      return new Result($id[0]);
    }

    return null;
  }

}
