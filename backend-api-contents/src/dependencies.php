<?php

$container = $app->getContainer();

if ( $container->get('settings')['environment'] == 'yesfile-develop' ) {
  $container['mdb'] = function ($c) {
    $settings = $c->get('settings')['db'];
    $pdo = new PDO("mysql:host=" . $settings['mdb']['host'] . ";dbname=" . $settings['mdb']['dbname'], $settings['mdb']['user'], $settings['mdb']['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // $pdo->query("set names euckr"); // 현재 DATABASE의 charset이 euckr입니다.

    return $pdo;
  };

  $container['sdb'] = function ($c) {
    $settings = $c->get('settings')['db'];
    // 테스트 환경의 mdb와 sdb 접속 정보는 동일
    $pdo = new PDO("mysql:host=" . $settings['mdb']['host'] . ";dbname=" . $settings['mdb']['dbname'], $settings['mdb']['user'], $settings['mdb']['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->query("set names utf8");

    return $pdo;
  };
}
else { //yesfile-staging, yesfile-product
  $container['mdb'] = function ($c) {
    $settings = $c->get('settings')['db'];
    $pdo_mdb = new PDO("mysql:host=" . $settings['mdb']['host'] . ";dbname=" . $settings['mdb']['dbname'], $settings['mdb']['user'], $settings['mdb']['pass']);
    $pdo_mdb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_mdb->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    return $pdo_mdb;
  };

  $container['sdb'] = function ($c) {
    $settings = $c->get('settings')['db'];
    try {
      $pdo_core = new PDO("mysql:host=" . $settings['coredb']['host'] . ";dbname=" . $settings['coredb']['dbname'], $settings['coredb']['user'], $settings['coredb']['pass']);
      $pdo_core->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
      $pdo_core->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
      
      $query = "SELECT ip, pc_key, mobile_key FROM db_server_list WHERE state=1 ORDER BY rand() LIMIT 1";
      $core_stmt = $pdo_core->prepare($query);
      $core_stmt->execute();
      $db_ip_info = $core_stmt->fetch();
      
      $pdo_sdb = new PDO("mysql:host=" . $db_ip_info['ip'] . ";dbname=" . $settings['sdb']['dbname'], $settings['sdb']['user'], $settings['sdb']['pass']);
    }
    catch(\PDOException $e) {
      $db_ip_list = $settings['sdb']['fallback'];
      
      srand((double)microtime() * 1000000);
      $rand_db_key = array_rand($db_ip_list);
      
      $max_process_count = 100;
      $pdo_sdb = new PDO("mysql:host=" . $db_ip_list[$rand_db_key] . ";dbname=" . $settings['sdb']['dbname'], $settings['sdb']['user'], $settings['sdb']['pass']);
      
      $stmt = $pdo_sdb->prepare("show processlist");
      $stmt->execute();
      $show_process_count = $stmt->rowCount();
      
      if ($show_process_count > $max_process_count) {
        $db_key = array_keys($db_ip_list);
        $db_ip2 = array();
        
        for ($dbi = 0; $dbi < count($db_key); $dbi++){
          if ($db_key[$dbi] != $rand_db_key){
            $db_ip2[$db_key[$dbi]] = $db_ip[$db_key[$dbi]];
          }
        }
        srand((double)microtime()*1000000);
        $rand_db_key = array_rand($db_ip2);
        
        $pdo_sdb = new PDO("mysql:host=" . $db_ip_list[$rand_db_key] . ";dbname=" . $settings['sdb']['dbname'], $settings['sdb']['user'], $settings['sdb']['pass']);
      }
    }
    
    $pdo_sdb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo_sdb->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo_sdb->query("set names utf8");
    
    return $pdo_sdb;
  };
}

// monolog
$container['logger'] = function ($c) {
    $settings_logger = $c->get('settings')['logger'];
  
    try {
      $redisClient = new Predis\Client([
        'scheme' => $settings_logger['scheme'],
        'host' => $settings_logger['host'],
        'port' => $settings_logger['port'],
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