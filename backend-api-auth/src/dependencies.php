<?php

$container = $app->getContainer();

// redis - refresh token database
$container['redis'] = function ($c) {
  $settings_redis = $c->get('settings')['redis'];
  $client = new Predis\Client([
    'scheme' => $settings_redis['scheme'],
    'host' => $settings_redis['host'],
  ]);      
  
  return $client;
};

// monolog - error, transaction log database
$container['logger'] = function ($c) {
  $settings_logger = $c->get('settings')['logger'];

  try {
    $redisClient = new Predis\Client([
      'scheme' => $settings_logger['scheme'],
      'host' => $settings_logger['host'],
    ]);

    $formatter = new Monolog\Formatter\LogstashFormatter($settings_logger['type']);
    $redisHandler = new Monolog\Handler\RedisHandler($redisClient, $settings_logger['key']);

    $redisHandler->setFormatter($formatter);
    $logger = new Monolog\Logger($settings_logger['channel'], array($redisHandler));
  }
  catch(\Exception $e) {
    print "로거에 접속할 수 없습니다." . $e->getMessage();
    exit;
  }

  return $logger;
};