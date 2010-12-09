<?php

include_once( 'class.sql.php' );
include_once( 'func.php' );

set_time_limit(0);
error_reporting(1);

if( $_GET['md5'] ) { echo md5(md5(md5($_GET['md5']))); exit; }

if( isset( $_GET['logout'] ) ) {
    setcookie( 'login', '' );
	header( 'Location: '.$_SERVER['PHP_SELF'] );
}

if( isset( $_GET['register'] ) ) {
    if( $_POST['goreg'] ) {
	    if( $_POST['pass1'] != $_POST['pass2'] ) echo "<center><font color='red'>пароли не совпадают</font><br><br>";
		else if( strlen( $_POST['pass1'] ) < 8 ) echo "<center><font color='red'>пароль должен быть не менее 8 символов!</font><br><br>";
		else {
		    $myDB = new DB();
			$sql = "INSERT INTO `onliner`.`users` (`id`, `mail`, `pass`) VALUES (NULL, '".$myDB->secSQL( $_POST['mail'] )."', '".md5(md5(md5($myDB->secSQL($_POST['pass1']))))."');";
			if( !$myDB->query( $sql ) ) {
			    echo "<font color='red'>При регистрации возникли проблемы. Обратитесь к администратору.</font>";
			}
			$cookie = base64_encode( $myDB->secSQL( $_POST['mail'] ).'###'.md5(md5(md5($myDB->secSQL($_POST['pass1'])))) );
			setcookie( 'login', $cookie, time()+60*60*24*30 );
		    header( 'Location: '.$_SERVER['PHP_SELF'] );
		}
	}
    showHeader();
	echo "<body><center><form action='".$_SERVER['PHP_SELF']."?register' method='post'><table><tr><td>E-mail:</td><td><input type='text' name='mail'></td></tr><tr><td>Пароль:</td><td><input type='password' name='pass1'></td></tr><tr><td>Повтор:</td><td><input type='password' name='pass2'></td></tr></table><br><div class='bigbut' id='go'><a href='#'>Регистрация</a></div></form>";
	exit;
}

$myDB = new DB();

$myLogin; $myID;

if( !checkAuth( 'check' ) ) {
    if( $_POST['mail'] && $_POST['pass'] ) {
	    if( checkAuth( 'login' ) ) {
			//echo "Successfull login";
			exit;
		}
		else echo '<font color="red">incorrect e-mail/pass</font><br><br>';
	}
    showHeader();
	echo "<body><center><form method='post'><table><tr><td>E-mail: </td><td><input type='text' name='mail'></td></tr><tr><td>Пароль: </td><td><input type='password' name='pass'></td></tr></table><br><div class='bigbut' id='go'><a href='#'>Войти</a></div> <div class='bigbut' id='go'><a href='".$_SERVER['PHP_SELF']."?register'>Зарегистрироваться</a></div></form> ";
	
	showFilesGuest();
	exit;
}

if( $_GET['option'] ) {
    $sql = "SELECT `comment` FROM `files` WHERE `userid`='$myID' AND `id`='".$myDB->secSQL($_GET['option'])."'";
	if( $row = $myDB->query( $sql ) AND $row != 1 ) {
	    if( $_POST['save'] ) {
		    //var_dump( $_POST ); exit;
			if( $_POST['comment'] == 'on'  ) {
			    $sql = "UPDATE  `onliner`.`files` SET `comment` = '1' WHERE `files`.`userid`='$myID' AND `files`.`id` ='".$myDB->secSQL($_GET['option'])."';";
				$c = 1;
			}
			else {
			    $sql = "UPDATE  `onliner`.`files` SET `comment` = '0' WHERE `files`.`userid`='$myID' AND `files`.`id` ='".$myDB->secSQL($_GET['option'])."';";
				$c = 0;
			}
			if( $myDB->query( $sql ) ) {
			    echo "<b><font color='green'><center>Успешно сохранено</font></b><br><br>";
				$row[0]['comment'] = $c;
			}
			else echo "Не удалось изменить опцию.";
		}
		echo "<br><br><center><form action='".$_SERVER['PHP_SELF']."?option=".$myDB->secSQL($_GET['option'])."' method='post'>";
		if( $row[0]['comment'] == 0 ) {
		    echo "<input type='checkbox' name='comment'> Разрешение комментировать файл";
		}
		else {
		    echo "<input type='checkbox' name='comment' checked> Разрешение комментировать файл";
		}
		echo "<br><br><input type='submit' name='save' value='Сохранить'></form><br><br><a href='javascript:close();'>Закрыть</a>";
	}
	else {
	    echo "Не верный file id.";
	}
	exit;
}

if( $_POST['filedel'] ) {
    foreach( $_POST['filedel'] as $delid ) {
	    $sql = "DELETE FROM `files` WHERE `userid`='$myID' AND `id`='".$myDB->secSQL($delid)."'";
		$myDB->query( $sql );
	}
}

showHeader();
echo "<body><center><table width='100%'><tr><td width='110%' align='center'>Привет, <b><i>".htmlspecialchars($myLogin)."</i></b> !</td><td><a href='".$_SERVER['PHP_SELF']."?logout'>Выйти</a></td></tr></table><br>";

if( $_FILES['ufile'] ) {
    $ufile      = $_FILES['ufile']['tmp_name'];
	$ufile_name = $_FILES['ufile']['name'];
	$ufile_size = $_FILES['ufile']['size'];
	$ufile_type = $_FILES['ufile']['type'];
	$ufile_flag = $_FILES['ufile']['error'];
	
	if( $ufile_flag == 0 ) {
	    //echo "File: $ufile<br>File:$ufile_name<br>File type: $ufile_type<br>Size: $ufile_size";
		if( !copy( $ufile, "upload/$ufile_name" ) ) {
		    echo "<font color='red'>ошибка при загрузке файла \"".htmlspecialchars($ufile_name)."\" ...<br></font>";
		}
		else {
			$sql = "INSERT INTO `onliner`.`files` (`id`, `filename`, `localname`, `userid`, `date`, `ip`, `ua`) VALUES (NULL, '$ufile_name', '$ufile_name', '$myID', '".date( 'Y-m-d H:i:s' )."', '".$_SERVER['REMOTE_ADDR']."', '".$_SERVER['HTTP_USER_AGENT']."');";
			
			if( !$myDB->query( $sql ) ) {
			    echo "<font color='red'>При добавлении файла возникла ошибка. Обратитесь к администратору.</font>";
			}
			echo "<font color='green'>Файл \"".htmlspecialchars($ufile_name)."\" успешно загружен!<br></font><br>";
		}
	}
	else {
	    switch( $ufile_flag ) {
			case 1:
			    echo "<font color='red'>ошибка при загрузке файла \"".htmlspecialchars($ufile_name)."\" [размер файла превышает максимально допустимый #1]<br></font>";
				break;
			case 2:
			    echo "<font color='red'>ошибка при загрузке файла \"".htmlspecialchars($ufile_name)."\" [размер файла превышает максимально допустимый #1]<br></font>";
				break;
			case 3:
			    echo "<font color='red'>ошибка при загрузке файла \"".htmlspecialchars($ufile_name)."\" [часть файла не загружена]<br></font>";
				break;
			case 4:
			    echo "<font color='red'>ошибка при загрузке файла \"".htmlspecialchars($ufile_name)."\" [некорректный путь к файлу]<br></font>";
				break;
			default:
			    echo "<font color='red'>ошибка при загрузке файла \"".htmlspecialchars($ufile_name)."\" ...<br></font>";
		}
	}
}
showFilesUser();
showUploadForm();

?>