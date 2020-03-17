<?php
namespace JonPontet\WebsiteAudit\Core;

abstract class Category {

  private $id;
  protected $audits = [];

  public function __construct(string $id, array $audits = null) {
    $this->id = $id;

    if ($audits !== null && is_array($audits)) {
      $this->audits = array_merge($this->audits, $audits);
    }
  }

  public function getId() {
    return $this->id;
  }

  public function addAudit(Audit $audit): void {
    $this->audits[] = $audit;
  }

  public function getAudits(): array {
    return $this->audits;
  }

  public function getScore(): float {
    $count = count($this->audits);
    if ($count === 0) {
      return 0;
    }

    $sum = 0;

    foreach ($this->audits as $audit) {
      $sum += $audit->getScore();
    }

    $avg = $sum / $count;

    return $avg;
  }

  public function getScoreFormatted(): string {
    return (string) ((int) ($this->getScore() * 100));
  }

  public function getTitle(): string {
    return __('category_title_' . $this->id, 'jpwa');
  }

  public function getSubtitle(): string {
    return __('category_subtitle_' . $this->id, 'jpwa');
  }

  public function getDescription(): string {
    return __('category_description_' . $this->id, 'jpwa');
  }

  public function getSource(): string {
    return __('category_source_' . $this->id, 'jpwa');
  }

}