<?php
namespace JonPontet\WebsiteAudit\Core;

abstract class Shortcode {

  private $name;
  private $registered = false;

  public function __construct($name) {
    $this->name = $name;
  }

  public function register(): void {
    if ($this->registered) {
      return;
    }

    add_shortcode($this->name, [$this, 'render']);

    $this->registered = true;
  }

  abstract public function render($atts): string;

}