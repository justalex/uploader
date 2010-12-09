<?php

function checkAuth( $mode ) {
    global $myDB, $myLogin, $myID;
    if( $mode == 'check' ) {
        if( !$_COOKIE['login'] ) return 0;
        
        $cookie = explode( '###', base64_decode( $_COOKIE['login'] ) );
        if( $row = $myDB->query( "SELECT id FROM users WHERE mail='".$myDB->secSQL( $cookie[0] )."' AND pass='".$myDB->secSQL( $cookie[1] )."'" ) ) {
            //return $myDB->secSQL( $cookie[0] );
            $myLogin = $myDB->secSQL( $cookie[0] );
            $myID    = $row[0]['id'];
            return 1;
        }
        return 0;
    }
    if( $mode == 'login' ) {
        if( $row = $myDB->query( "SELECT pass FROM users WHERE mail='".$myDB->secSQL( $_POST['mail'] )."' AND pass='".md5(md5(md5($myDB->secSQL($_POST['pass']))))."'" ) AND $row != 1 ) {
            $cookie = base64_encode( $myDB->secSQL( $_POST['mail'] ).'###'.md5(md5(md5($myDB->secSQL($_POST['pass'])))) );
            //print_r($row); exit;
            setcookie( 'login', $cookie, time()+60*60*24*30 );
            header( 'Location: '.$_SERVER['PHP_SELF'] );
            return 1;
        }
        else return 0;
    }
}

function showFilesGuest() {
    global $myDB,$myID;
    echo "<hr><center><br>";
    if( $row = $myDB->query( "SELECT * FROM  `files` LIMIT 0 , 25" ) ) {
        if( $row == 1 ) {
            echo "<b>Загруженных файлов не обнаружено</b>";
            return 0;
        }
        echo "<table width='70%' border='1'><tr align='center' bgcolor='#e7e7f0'><td><b>id</b></td><td><b>Имя файла</b></td><td><b>Дата загрузки</b></td><td>&nbsp</td></tr>";
        $fileUrl = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/upload/';
        foreach( $row as $file ) {
            echo "<tr align='center'><td>#".$file['id']."</td><td>".$file['filename']."</td><td>".$file['date']."</td><td><table width='70%'><tr align='center'><td><a href='$fileUrl".$file['filename']."'><img src='images/download.jpg' alt='Скачать'></a></td><td><a href=''><img src='images/comment.jpg' alt='Комментарии'></a></td>";
            echo "</tr></table></td></tr>";
        }
        echo "</table>";
    }
}

function showFilesUser() {
    global $myDB,$myID;
    if( $_GET['order'] == 'desc' ) {
        $order = 'DESC';
        $_order = 'asc';
        $image = 's_desc.png';
    } else {
        $order = 'ASC';
        $_order = 'desc';
        $image = 's_asc.png';
    }
    if( $_GET['by'] == 'filename' ) {
        $by = 'filename';
    } else {
        $by = 'date';
    }
    if( $row = $myDB->query( "SELECT * FROM  `files` ORDER BY $by $order LIMIT 0 , 25" ) ) {
        if( $row == 1 ) {
            echo "<br><br><hr><center><br><b>Загруженных файлов не обнаружено</b><br>";
            return 0;
        }
        echo "<table width='70%' border='1'><tr align='center' bgcolor='#e7e7f0'><td>&nbsp</td><td><b>id</b></td><td><b>Имя файла <a href='?order=$_order&by=filename'><img src='images/$image' border='0'></a></b></td><td><b>Дата загрузки</b> <a href='?order=$_order&by=date'><img src='images/$image' border='0'></a></td><td>&nbsp</td></tr><form method='post' name='formUserFiles'>";
        $fileUrl = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/upload/';
        foreach( $row as $file ) {
            echo "<tr align='center'><td>&nbsp";
            if($file['userid'] == $myID )  echo "<input type='checkbox' name='filedel[]' value='".$file['id']."'>";
            echo "</td><td>#".$file['id']."</td><td>".$file['filename']."</td><td>".$file['date']."</td><td><table width='70%'><tr align='center'><td><a href='$fileUrl".$file['filename']."'><img src='images/download.jpg' alt='Скачать'></a></td><td><a href=''><img src='images/comment.jpg' alt='Комментарии'></a></td>";
            if($file['userid'] == $myID ) echo "<td><a href='javascript:open(\"".$_SERVER['PHP_SELF']."?option=".$file['id']."\", \"displayWindow\",\"width=400,height=300,status=no,toolbar=no,menubar=no\");'><img src='images/edit.jpg' alt='Опции'></a></td>";
            echo "</tr></table></td></tr>";
        }
        echo "</table><table width='70%'><tr><td><div class='bigbut' id='go'><a href='#' onclick='document.formUserFiles.submit();'>удалить отмеченные</a></div></form></td></tr></table>";
    }
}

function showUploadForm() {
    echo "<br><hr><br><form enctype='multipart/form-data' method='post' name='uploadForm'><input type='file' name='ufile'><br><br>
    <div class='bigbut' id='go'><a href='#' onclick='document.uploadForm.submit();'>Загрузить файл</a></div></form>";
}

function showHeader() {
echo <<<HTML
<html>
<head>
<meta charset="UTF-8"> 
<link href="css/style.css" rel="stylesheet" type="text/css" />
<title>Onliner Uploader</title>
</head>
HTML;
}

?>