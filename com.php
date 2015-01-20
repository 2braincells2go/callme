<?php
@session_start();
require_once(getcwd().'/SqlFuncProc/SqlFuncProc.php');
$order = $_REQUEST['order'];
$param = $_REQUEST['param'];
$func = SqlFuncProc::getInstance($_SESSION['cfg']['SQL_CONN'], $_SESSION['cfg']['SQL_USER'],$_SESSION['cfg']['SQL_PASS']);

if ($order == 'getUsers') {
    $data = $func->runFunc("get_users", array('%', '%', '%', $_SESSION['user']['id']), false, true);
    echo Json_encode($data);
}

if ($order == 'setUser') {
    $data = $func->runProc("set_user", array($param['peer_id'], $_SESSION['user']['id']), true);
    if ($data[0] == "00000") {
        echo Json_encode('success');
    } else {
        echo Json_encode('default');
    }
}
