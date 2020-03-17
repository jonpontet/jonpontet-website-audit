<?php
namespace JonPontet\WebsiteAudit\Core;

class RateLimiter {

  private $optionKey = 'jpwa_rate_limiter';
  private $concurrent;
  private $expireTime;

  public function __construct($concurrent = 5, $expireTime = MINUTE_IN_SECONDS) {
    $this->concurrent = $concurrent;
    $this->expireTime = $expireTime;
  }

  public function add() {
    $key = uniqid();

    $requests = $this->getOption();

    $requests[$key] = [
      'time' => time()
    ];

    $this->setOption($requests);

    return $key;
  }

  public function remove($key) {
    $requests = $this->getOption();
    unset($requests[$key]);
    $this->setOption($requests);
  }

  public function canAdd() {
    $requests = $this->getOption();

    $expireTime = $this->expireTime;
    $requests = array_filter($requests, function ($val) use($expireTime) {
      return time() - $val['time'] < $expireTime;
    });

    $this->setOption($requests);

    $count = count($requests);

    return $count < $this->concurrent;
  }

  private function getOption() {
    return get_option($this->optionKey, []);
  }

  private function setOption($val) {
    update_option($this->optionKey, $val);
  }

}
