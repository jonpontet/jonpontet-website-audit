<?php
namespace JonPontet\WebsiteAudit\Core;

class AuditContext {

  protected $data = [];

  public function __construct($initial = []) {
    $this->merge($initial);
  }

  public function set(string $key, $value): void {
    $this->data[$key] = $value;
  }

  public function merge(array $array): void {
    $this->data = array_merge($this->data, $array);
  }

  public function get(string $key, $fallback = null) {
    return (isset($this->data[$key])) ? $this->data[$key] : $fallback;
  }

}