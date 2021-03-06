#!/usr/bin/php
<?php
/**
 * Usage: 更新账号接口缓存(api/accounts.php). 参数为需要获取服务器ID
 * lnkUserToServer, lnkContactToFunctionalCI及Server变更都需要更新缓存
 * 因此ID分别需要设置为 $this->server_id$, $this->functionalci_id$, $this->id$
 * File Name: update_accounts_cache.php
 * Author: annhe  
 * Mail: i@annhe.net
 * Created Time: 2017-06-9 17:08:30
 **/

require dirname(__FILE__).'/../etc/config.php';

$ID = getenv("ID");
$DEBUG = getenv("DEBUG");
$script = explode("/", $argv[0]);
$log = dirname(__FILE__) . '/../' . $config['tasklogdir'] . "/" .  end($script) . ".log";
$INTERVAL = getenv("INTERVAL");
if(!$INTERVAL) $INTERVAL = 30;
// 可能是缓存原因，接口返回数据没有变化，导致用户删除自己负责的app时未更新contacts字段, 所以这里等几秒
if(!$DEBUG)
{
	sleep($config['update']['delay']);
}

// 清除过期EventNotificationShellExec对象
$query = "SELECT EventNotificationShellExec WHERE date < DATE(DATE_SUB(NOW(), INTERVAL $INTERVAL DAY))";
$serverQuery = "SELECT EventNotificationShellExec AS e JOIN TriggerOnObject AS t ON e.trigger_id=t.id WHERE t.target_class='Server' AND date < DATE(DATE_SUB(NOW(), INTERVAL 1 DAY))";
$d = $iTopAPI->coreDelete("EventNotificationShellExec", $query);
$sd = $iTopAPI->coreDelete("EventNotificationShellExec", $serverQuery);

if($DEBUG) {print_r($d);}

$ret = accountsSetCache($ID);
file_put_contents($log, $config['datetime'] . " - $ID - $ret\n", FILE_APPEND);
file_put_contents($log, $config['datetime'] . " - $ID - $d\n", FILE_APPEND);
