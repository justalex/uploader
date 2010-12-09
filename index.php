<?php

include_once( 'class.sql.php' );
include_once( 'func.php' );

if( $_GET['md5'] ) { echo md5(md5(md5($_GET['md5']))); exit; }

if( isset( $_GET['logout'] ) ) {
    setcookie( 'login', '' );
    header( 'Location: '.$_SERVER['PHP_SELF'] );
}

if( isset( $_GET['register'] ) ) {
    if( $_POST['mail'] && $_POST['pass1'] && $_POST['pass2'] ) {
        if( $_POST['pass1'] != $_POST['pass2'] ) echo "<div id='incorrect'>пароли не совпадают</div>";
        else if( strlen( $_POST['pass1'] ) < 8 ) echo "<div id='incorrect'>пароль должен быть не менее 8 символов!</div>";
        else {
            $myDB = new DB();
            $sql = "INSERT INTO `".DB_BASE."`.`users` (`id`, `mail`, `pass`) VALUES (NULL, '".$myDB->secSQL( $_POST['mail'] )."', '".md5(md5(md5($myDB->secSQL($_POST['pass1']))))."');";
            //echo $sql;
            if( !$myDB->query( $sql ) ) {
                echo "<div id='incorrect'>При регистрации возникли проблемы. Обратитесь к администратору.</div>";
            }
            $cookie = base64_encode( $myDB->secSQL( $_POST['mail'] ).'###'.md5(md5(md5($myDB->secSQL($_POST['pass1'])))) );
            setcookie( 'login', $cookie, time()+60*60*24*30 );
            header( 'Location: '.$_SERVER['PHP_SELF'] );
        }
    }
    showHeader();
    echo "<body><center><form action='".$_SERVER['PHP_SELF']."?register' method='post' name='regForm'><table><tr><td>E-mail:</td><td><input type='text' name='mail' class='text'></td></tr><tr><td>Пароль:</td><td><input type='password' name='pass1' class='text'></td></tr><tr><td>Повтор:</td><td><input type='password' name='pass2' class='text'></td></tr></table><br><div class='bigbut' id='go'><a href='#' onclick='document.regForm.submit();'>Регистрация</a></div></form>";
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
        else echo '<div id="incorrect">неверный e-mail или пароль</div><br><br>';
    }
    showHeader();
    if( $_POST['author'] AND $_POST['mess'] ) {
        $sql = "INSERT INTO `comments` (`softid`,`date`,`userid`,`message`) VALUES ('".$_POST['softid']."', '".date( 'Y-m-d H:i:s' )."','0','".addslashes($_POST['mess'])."')";
        if( !$myDB->query( $sql ) ) {
            echo "<div id='incorrect'>При регистрации возникли проблемы. Обратитесь к администратору.</div>";
        } else {
            echo "<div id='correct'>Сообщение успешно добавлено.</div>";
        }
    }
    echo "<body><center><form method='post' name='loginForm'><table><tr><td>E-mail: </td><td><input type='text' name='mail' class='text'></td></tr><tr><td>Пароль: </td><td><input type='password' name='pass' class='text'></td></tr></table><br><div class='bigbut' id='go'><a href='#' onclick='document.loginForm.submit();'>Войти</a></div> <div class='bigbut' id='go'><a href='".$_SERVER['PHP_SELF']."?register'>Зарегистрироваться</a></div></form> ";

    if( $_GET['comment'] ) {
        showCommentPage( $_GET['comment'] );
        
    } else {
        showFilesGuest();
    }
    exit;
}

if( $_GET['option'] ) {
    $sql = "SELECT `comment` FROM `files` WHERE `userid`='$myID' AND `id`='".$myDB->secSQL($_GET['option'])."'";
    if( $row = $myDB->query( $sql ) AND $row != 1 ) {
        if( $_POST['save'] ) {
            //var_dump( $_POST ); exit;
            if( $_POST['comment'] == 'on'  ) {
                $sql = "UPDATE  `".DB_BASE."`.`files` SET `comment` = '1' WHERE `files`.`userid`='$myID' AND `files`.`id` ='".$myDB->secSQL($_GET['option'])."';";
                $c = 1;
            }
            else {
                $sql = "UPDATE  `".DB_BASE."`.`files` SET `comment` = '0' WHERE `files`.`userid`='$myID' AND `files`.`id` ='".$myDB->secSQL($_GET['option'])."';";
                $c = 0;
            }
            if( $myDB->query( $sql ) ) {
                echo "<div id='correct'>Успешно сохранено</div>";
                $row[0]['comment'] = $c;
            }
            else echo "<div id='incorrect'>Не удалось изменить опцию.</div>";
        }
        showHeader();
        echo "<body><br><br><center><form action='".$_SERVER['PHP_SELF']."?option=".$myDB->secSQL($_GET['option'])."' method='post' name='formOption'><input type='checkbox' name='comment'";
        if( $row[0]['comment'] != 0 ) {
            echo "checked";
        }
        echo "> Разрешение комментировать файл<br><br><input type='hidden' name='save' value='1'><div class='bigbut' id='go'><a href='#' onclick='document.formOption.submit();'>Сохранить</a></div></form><br><br><a href='javascript:close();'>Закрыть</a>";
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
            $sql = "INSERT INTO `".DB_BASE."`.`files` (`id`, `filename`, `localname`, `userid`, `date`, `ip`, `ua`) VALUES (NULL, '$ufile_name', '$ufile_name', '$myID', '".date( 'Y-m-d H:i:s' )."', '".$_SERVER['REMOTE_ADDR']."', '".$_SERVER['HTTP_USER_AGENT']."');";

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
if( $_GET['comment'] ) {
    showCommentPage( $_GET['comment'] );
} else {
    showFilesUser();
    showUploadForm();
}

?>