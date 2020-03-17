<?php
namespace JonPontet\WebsiteAudit\Core;

use \JonPontet\WebsiteAudit\WebsiteAuditPlugin;

abstract class Audit {

  protected $id;
  protected $context;
  protected $errors = [];
  private $score = 0;

  public function __construct(string $id, AuditContext $context) {
    $this->id = $id;
    $this->context = $context;
  }

  public function getId() {
    return $this->id;
  }

  public function getErrors(): array {
    return $this->errors;
  }

  public function hasErrors(): bool {
    return count($this->errors) > 0;
  }

  protected function addError($error): void {
    $this->errors[] = $error;
    
    WebsiteAuditPlugin::logError($error);
  }

  public function getDescription(): string {
    return __('audit_description_' . $this->id, 'jpwa');
  }

  public function getScore(): float {
    return (!$this->score) ? 0 : $this->score;
  }

  public function setScore($score): void {
    $this->score = $score;
  }

  protected function addScore($score): void {
    $this->score += $score;
  }

  abstract public function evaluate(): void;

}