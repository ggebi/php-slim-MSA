<?php

namespace Bankmedia\Common;
/**
 * @file  profile.php
 * @brief API 응답시간 설정, 계산
 */
class Profile {
  protected $time = array();

  public function start(string $time_name)
  {
    $this->time[$time_name] = microtime(true);
  }

  public function end(string $time_name)
  {
    return (microtime(true) - $this->time[$time_name]);
  }
}