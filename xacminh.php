<?php
// Conex?o
require_once 'DB.php';

session_start();

if (isset($_POST['btn-entrar'])) :
	$erros = array();
	$login = mysqli_escape_string($conn, $_POST['login']);
	$senha = mysqli_escape_string($conn, $_POST['senha']);

	if (isset($_POST['lembrar-senha'])) :
		setcookie('login', $login, time() + 3600);
		setcookie('senha', md5($senha), time() + 3600);
	endif;

	if (empty($login) or empty($senha)) :
		$erros[] = "<li> Trường đăng nhập / mật khẩu cần được điền vào </li>";
	else :
		$sql = "SELECT username FROM `admin` WHERE username = '$login'";
		$resultado = mysqli_query($conn, $sql);
		if (mysqli_num_rows($resultado) > 0) :
			// $senha = md5($senha);       
			$sql = "SELECT * FROM `admin` WHERE username = '$login' AND password = '$senha'";

			$resultado = mysqli_query($conn, $sql);

			if (mysqli_num_rows($resultado) == 1) :
				$dados = mysqli_fetch_array($resultado);
				mysqli_close($conn);
				$_SESSION['logado'] = true;
				$_SESSION['id_usuario'] = $dados['id'];
				header('Location: index.php');
			else :
				$erros[] = "<li> Tên người dùng và mật khẩu không phù hợp </li>";
			endif;

		else :
			$erros[] = "<li> Người dùng không tồn tại </li>";
		endif;

	endif;

endif;
?>

<html lang="pt-br">

<head>
	<meta charset="UTF-8">
	<title>DPS Login</title>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/modernizr/2.8.3/modernizr.min.js" type="text/javascript"></script>
	<link rel="stylesheet" href="css/index.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<link rel='stylesheet' href='https://fonts.googleapis.com/css?family=Open+Sans+Condensed:300'>
	<style>
		body {
			background: url("bgimg.jpg");
			background-size: 100%;
			background-repeat: no-repeat;
			position: relative;
			background-attachment: fixed;
		}
	</style>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/prefixfree/1.0.7/prefixfree.min.js"></script>
</head>

<body>
	<div id="registration-form">
		<div class='fieldset'>
			<legend>DPS Admin</legend>
			<?php
					if (!empty($erros)) :
						foreach ($erros as $erro) :
							echo $erro;
						endforeach;
					endif;
					?>
			<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" data-validate="parsley">
				<div class='row'>
					<!--	<label for='firstname'>Username</label> -->
					<input type="text" placeholder="Username" name="login" id='firstname' value="<?php echo isset($_COOKIE['login']) ? $_COOKIE['login'] : '' ?>" data-required="true" data-error-message="UserNnme is required" required>
				</div>
				<div class='row'>
					<!--	<label for="lastname">Password</label> -->
					<input type="text" placeholder="Password" name="senha" value="<?php echo isset($_COOKIE['senha']) ? $_COOKIE['senha'] : '' ?>" data-required="true" data-type="email" data-error-message="Password is required" required>
				</div>
				<input type="submit" value="Login" name="btn-entrar">
			</form>
		</div>
	</div>
	<script src='http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>
	<script src='https://cdnjs.cloudflare.com/ajax/libs/parsley.js/1.2.2/parsley.min.js'></script>

</body>

</html>