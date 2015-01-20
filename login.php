<?php
@session_start();
include_once (getcwd() . '/config.php');
require_once (getcwd() . '/SqlFuncProc/SqlFuncProc.php');
$error = '';
$func = SqlFuncProc::getInstance($_SESSION['cfg']['SQL_CONN'], $_SESSION['cfg']['SQL_USER'], $_SESSION['cfg']['SQL_PASS']);
$data = $func->runProc("initialize", array(), false);
if (isset($_POST['logClick'])) {
	$name = $_POST['Email'];
	$pass = $_POST['Password'];
	$data = $func->runFunc("get_users", array($name, $name, $pass, 0), 1, false);
	if (sizeof($data) == 0) {
		$error = 'User not registered with these datas!';
	} else {
		$_SESSION['user'] = $data[0];
		header('location:' . current_HTTP());
		exit;
	}
} else if (isset($_POST['regClick'])) {
	$email = $_POST['inputEmail'];
	$name = $_POST['inputName'];
	$pass = $_POST['inputPassword'];
	$dataURI = $_POST['inputImg'];
	$encodedData = explode(',', $dataURI)[1];
	$data = $func->runProc("insert_user", array($name, $email, $pass, $encodedData), false);
	if ($data[0] != "00000") {
		$error = 'Somebody registered with this email!';
	}
} else if (isset($_POST['forgottClick'])) {
	$email = $_POST['ForgottEmail'];
	$data = $func->runFunc("get_users", array($email, $email, '%', 0), 1, false);
	if (sizeof($data) == 0) {
		$error = 'User not registered with whit these email!';
	} else {
		if(!send_mail($data[0], 'Forgotten password',  $_SERVER["HTTP_HOST"] , current_HTTP(), 'reg_forgot.php')) $error = 'Something wrong with email sending!';
	}
}
function send_mail($param, $subject, $from, $path, $file){
	$to = $param['email'];
	$subject = $subject;
	$message = file_get_contents($file);
	$message = str_replace('#name#', $param['name'], $message);
	$message = str_replace('#mail#', $param['email'], $message);
	$message = str_replace('#pass#', $param['pass'], $message);
	$message = str_replace('#path#', $path, $message);
	$message = str_replace('#websrv#', $_SERVER["HTTP_HOST"], $message);
	$headers = "From: $from <$from>\r\n"."MIME-Version: 1.0"."\r\n"."Content-type: text/html; charset=UTF-8" . "\r\n";
	return mail($to,$subject,$message,$headers);
}
function get_extension($str) {
	$str = strtolower($str);
	$arr = explode('.', $str);
	if (sizeof($arr) < 2) {
		return "";
	}
	return $str = $arr[sizeof($arr) - 1];
}
function data_uri($file) {
	$type = get_extension($file);
	$mime = 'image/' . $type;
	$contents = file_get_contents($file);
	$base64 = base64_encode($contents);
	return ('data:' . $mime . ';base64,' . $base64);
}
function current_HTTP() {
	$arr = explode('/', 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
	unset($arr[sizeof($arr) - 1]);
	$index = implode('/', $arr).'/';
	return $index;
}
?>
<!DOCTYPE html>
<html lang="">
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
		<link href="css/login.css?v=1" rel="stylesheet">
		<!--[if lt IE 9]>
             <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
             <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
	</head>
	<body>
		<div class="container imaged">
			<div class="row row-centered">
				<h1 class="page-header">CallMe Video Chat<br>
					<small>Can be downloaded from: <a href="https://github.com/andrastoth/callme" target="_blank"> GitHub </a> OR <a href="http://www.jsclasses.org/package/393-JavaScript-Establish-video-chat-communications-between-users.html" target="_blank"> JSclasses</a>
					</small>
				</h1>
				<div class="col-md-6 col-centered">
					<ul class="nav nav-tabs">
						<li class="active"><a data-toggle="tab" href="#login-form">Sign in</a></li>
						<li><a data-toggle="tab" href="#register-form">Register</a></li>
						<li><a data-toggle="tab" href="#forgotten-form">Forgotten password</a></li>
					</ul>
					<div class="tab-content">
						<div id="login-form" class="tab-pane fade in active">
							<form action="login.php" method="POST" class="form-signin">
								<div class="row">
									<div class="col-md-6 col-centered">
										<h2 class="form-signin-heading">Please sign in</h2>
										<label for="Email">Email address Or Name</label>
										<input type="text" name="Email" class="form-control" placeholder="Email OR Name" required="" autofocus="">
										<label for="Password">Password</label>
										<input type="password" name="Password" class="form-control" placeholder="Password" required="">
										<br>
										<button name="logClick" class="btn btn-lg btn-success" type="submit">Sign in</button>
									</div>
								</div>
							</form>
						</div>
						<div id="register-form" class="tab-pane fade">
							<form action="login.php"  method="post" enctype="multipart/form-data" class="form-signin">
								<div class="row">
									<div class="col-md-6">
										<h2 class="form-signin-heading">Please fill in</h2>
										<label for="inputEmail">Email address</label>
										<input type="email" name="inputEmail" class="form-control" placeholder="Email address" required="" autofocus="">
										<label for="inputName">Name</label>
										<input type="text" name="inputName" class="form-control" placeholder="Name" maxlength="12" data-toggle="tooltip" data-placement="top" title="" data-original-title="Max 12 chars" required="">
										<label for="inputPassword">Password</label>
										<input type="password" name="inputPassword" class="form-control" placeholder="Password" required="">
										<input type="text" name="inputImg" class="hidden">
										<br>
									</div>
									<div class="col-md-6" style="text-align: center;">
										<img id="selected-img" src="<?php echo data_uri('css/images/user_anonym_allow.png'); ?>" data-toggle="tooltip" data-placement="top" title="" data-original-title="Click to change">
									</div>
								</div>
								<button name="regClick" class="btn btn-lg btn-primary" type="submit">Register</button>
							</form>
						</div>
						<div id="forgotten-form" class="tab-pane fade">
							<form action="login.php" method="POST" class="form-forgott">
								<div class="row">
									<div class="col-md-6 col-centered">
										<br>
										<label for="ForgottEmail">Email address</label>
										<input type="email" name="ForgottEmail" class="form-control" placeholder="Email address" required="" autofocus="">
										<br>
										<button name="forgottClick" class="btn btn-lg btn-primary" type="submit">Send</button>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="alert alert-danger <?php if ($error == '') echo 'fade'; ?>">
			<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
			<strong>Error!  </strong><?php echo $error; ?>
		</div>
		<script src="js/jquery.js"></script>
		<script src="js/bootstrap.js"></script>
		<script type="text/javascript">
			$('#selected-img').tooltip();
			$('[name="inputName"]').tooltip();
			var form = '';
			form += '<form id="up-form" action="upload.php" method="post" enctype="multipart/form-data">';
			form += '  <input type="file" name="pic" accept="image/*" onchange="' + "document.querySelectorAll('#up-form')[0].submit();" + '">';
			form += '</form>';
			var iframe = $('<iframe id="upload_target" name="upload_target" style="display: none; height: 10px; width: 10px;"></iframe>');
			$('[name="inputImg"]').val($('#selected-img').attr('src'));
			$('img').click(function(event) {
				$(this).attr('src', 'css/images/loading.gif');
				iframe.remove();
				iframe.appendTo('body');
				iframe.contents().find('body').html(form);
				iframe.contents().find('input:eq(0)').click();
				iframe.load(function(){
					var img = iframe.contents().find('#data').text();
					$('#selected-img').attr('src', img);
					$('[name="inputImg"]').val(img);
				});
			});
		</script>
	</body>
</html>