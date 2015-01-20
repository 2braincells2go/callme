<?php
@session_start();
if (isset($_REQUEST['logout'])) {
    destSession();
}
if (!isset($_SESSION['user'])) {
    header('location:' .current_HTTP(). 'login.php');
    exit;
}
function destSession() {
    $id = session_id();
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
    @unlink(ini_get('session.save_path') . '/sess_' . $id);
}
function current_HTTP() {
    $arr = explode('/', 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    unset($arr[sizeof($arr) - 1]);
    $index = implode('/', $arr).'/';
    return $index;
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="CallMe Videochat">
        <meta name="author" content="Tóth András">
        <title>CallMe</title>
        <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
        <link rel="icon" href="favicon.ico" type="image/x-icon">
        <link href="css/bootstrap.css" rel="stylesheet">
        <link href="css/callme.css" rel="stylesheet">
        <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
    </head>
    <body>
        <div id="wrapper" class="toggled">
            <div id="sidebar-wrapper" class="shadow-page">
                <div class="col-md-12 text-center">
                    <h3>
                        <span><?php echo $_SESSION['user']['name']; ?></span>
                        <img class="img-circle img-circle-user" src="data:image/png;base64,<?php echo $_SESSION['user']['img']; ?>">
                    </h3>
                </div>
            <ul class="nav sidebar-nav"></ul>
        </div>
        <div id="page-content-wrapper">
            <video id="their-video" width="100%" height="100%" autoplay></video>
            <canvas id="own-video" class="shadow-page" width="276" height="176"></canvas>
            <div class="row control-panel">
                <div class="col-md-6">
                    <div id="preview_user"></div>
                    <button class="btn btn-warning btn-round" onclick="window.location=location.protocol+'//'+location.host+location.pathname+location.search+'?logout=true';"><span class="glyphicon glyphicon-log-out"></span></button>
                    <button id="menu-toggle" class="btn btn-info btn-round" ><span class="glyphicon glyphicon-tasks"></span></button>
                    <button id="full-screen" class="btn btn-primary btn-round" ><span class="glyphicon glyphicon-fullscreen"></span></button>
                    <button id="toggle-video" class="btn btn-default btn-round" ><span class="glyphicon glyphicon-facetime-video"></span></button>
                    <button id="toggle-audio" class="btn btn-default btn-round" ><span class="glyphicon glyphicon-volume-up"></span></button>
                    <button class="btn btn-success btn-round start-call hidden"><span class="glyphicon glyphicon-earphone"></span></button>
                    <button class="btn btn-danger btn-round end-call hidden"><span class="glyphicon glyphicon-phone-alt"></span></button>
                </div>
            </div>
        </div>
        <div id="call-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true" data-call_id="" data-id="" data-name="">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3 class="modal-title">Calling</h3>
                    </div>
                    <div class="modal-body text-center">
                        <img src="">
                        <h3>User</h3>
                    </div>
                    <div class="modal-footer">
                        <button class="btn btn-success btn-round start-call hidden"><span class="glyphicon glyphicon-earphone"></span></button>
                        <button class="btn btn-danger btn-round end-call hidden"><span class="glyphicon glyphicon-phone-alt"></span></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="alert alert-danger alert-error text-center fade" id="alert-box">
            <a href="#" class="close" data-dismiss="alert">&times;</a>
            <strong>Error!</strong>
            <br><span></span>
            <br>
            <button class="btn btn-default" onclick="location.reload();">Reload</button>
        </div>
    </div>
    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/bootstrap.js"></script>
    <script type="text/javascript" src="js/peer.min.js"></script>
    <script type="text/javascript" src="js/jquery.fullscreen.min.js"></script>
    <script type="text/javascript">var peerKey ="<?php echo $_SESSION['cfg']['PEER_KEY']; ?>";</script>
    <script type="text/javascript" src="js/callme.js"></script>
    </body>
</html>