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
    if( $_GET['order'] == 'asc' ) {
        $order = 'ASC';
        $_order = 'desc';
        $image = 's_asc.png';
    } else {
        $order = 'DESC';
        $_order = 'asc';
        $image = 's_desc.png';
    }
    if( $_GET['by'] == 'filename' ) {
        $by = 'filename';
    } else {
        $by = 'date';
    }
    if( $_GET['page'] > 1 ) {
        $limit = (($_GET['page']-1)*MAX_SHOW).', '.MAX_SHOW;
    } else {
        $limit = '0, '.MAX_SHOW;
    }
    echo "<hr><center><br>";
    if( $row = $myDB->query( "SELECT COUNT(*) AS count FROM  `files`" ) ) {
        $countFiles = $row[0]['count'];
    } else {
        echo "<br><br><hr><center><br><b>Загруженных файлов не обнаружено</b><br>";
        return 0;
    }
    if( $row = $myDB->query( "SELECT * FROM  `files` ORDER BY $by $order LIMIT $limit" ) ) {
        if( $row == 1 ) {
            echo "<b>Загруженных файлов не обнаружено</b>";
            return 0;
        }
        echo "<table width='70%' border='1'><tr align='center' bgcolor='#e7e7f0'><td><b>id</b></td><td><b>Имя файла <a href='?order=$_order&by=filename'><img src='images/$image' border='0'></a></b></td><td><b>Дата загрузки</b> <a href='?order=$_order&by=date'><img src='images/$image' border='0'></a></td><td>&nbsp</td></tr>";
        $fileUrl = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/upload/';
        foreach( $row as $file ) {
            if( $file['comment'] == 1 ) $commentAccess = "<a href='?comment=".$file['id']."'><img src='images/comment.jpg' title='Комментарии'></a>";
            else $commentAccess = '';
            
            echo "<tr align='center'><td>#".$file['id']."</td><td>".$file['filename']."</td><td>".$file['date']."</td><td><table width='70%'><tr align='center'><td><a href='$fileUrl".$file['filename']."'><img src='images/download.jpg' title='Скачать'></a></td><td>$commentAccess</td>";
            echo "</tr></table></td></tr>";
        }
        echo "</table>";
        if( $countFiles > MAX_SHOW ) {
            echo "<br><hr><br>";
            $countPages = (int)( ( $countFiles + MAX_SHOW - 1 ) / MAX_SHOW );
            for( $linkID = 1; $linkID <= $countPages; $linkID++ ) {
                if( $linkID == 1 ) echo "<a href='?page=$linkID'>$linkID</a>";
                else echo " | <a href='?page=$linkID'>$linkID</a>";
            }
            echo "<br>";
        }
    }
}

function showFilesUser() {
    global $myDB,$myID;
    if( $_GET['order'] == 'asc' ) {
        $order = 'ASC';
        $_order = 'desc';
        $image = 's_asc.png';
    } else {
        $order = 'DESC';
        $_order = 'asc';
        $image = 's_desc.png';
    }
    if( $_GET['by'] == 'filename' ) {
        $by = 'filename';
    } else {
        $by = 'date';
    }
    if( $_GET['page'] > 1 ) {
        $limit = (($_GET['page']-1)*MAX_SHOW).', '.MAX_SHOW;
    } else {
        $limit = '0, '.MAX_SHOW;
    }

    if( $row = $myDB->query( "SELECT COUNT(*) AS count FROM  `files`" ) ) {
        $countFiles = $row[0]['count'];
    } else {
        echo "<br><br><hr><center><br><b>Загруженных файлов не обнаружено</b><br>";
        return 0;
    }
    if( $row = $myDB->query( "SELECT * FROM  `files` ORDER BY $by $order LIMIT $limit" ) ) {
        if( $row == 1 ) {
            echo "<br><br><hr><center><br><b>Загруженных файлов не обнаружено</b><br>";
            return 0;
        }
        echo "<table width='70%' border='1'><tr align='center' bgcolor='#e7e7f0'><td>&nbsp</td><td><b>id</b></td><td><b>Имя файла <a href='?order=$_order&by=filename'><img src='images/$image' border='0'></a></b></td><td><b>Дата загрузки</b> <a href='?order=$_order&by=date'><img src='images/$image' border='0'></a></td><td>&nbsp</td></tr><form method='post' name='formUserFiles'>";
        $fileUrl = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/upload/';
        foreach( $row as $file ) {
            if( $file['comment'] == 1 ) $commentAccess = "<a href='?comment=".$file['id']."'><img src='images/comment.jpg' title='Комментарии'></a>";
            else $commentAccess = '';
            echo "<tr align='center'><td>&nbsp";
            if($file['userid'] == $myID )  echo "<input type='checkbox' name='filedel[]' value='".$file['id']."'>";
            echo "</td><td>#".$file['id']."</td><td>".$file['filename']."</td><td>".$file['date']."</td><td><table width='70%'><tr align='center'><td><a href='$fileUrl".$file['filename']."'><img src='images/download.jpg' title='Скачать'></a></td><td>$commentAccess</td>";
            if($file['userid'] == $myID ) echo "<td><a href='javascript:open(\"".$_SERVER['PHP_SELF']."?option=".$file['id']."\", \"displayWindow\",\"width=400,height=300,status=no,toolbar=no,menubar=no\");'><img src='images/edit.jpg' title='Опции'></a></td>";
            echo "</tr></table></td></tr>";
        }
        echo "</table><table width='70%'><tr><td><div class='bigbut' id='go'><a href='#' onclick='document.formUserFiles.submit();'>удалить отмеченные</a></div></form></td></tr></table><br>";
        if( $countFiles > MAX_SHOW ) {
            echo "<br><hr><br>";
            $countPages = (int)( ( $countFiles + MAX_SHOW - 1 ) / MAX_SHOW );
            for( $linkID = 1; $linkID <= $countPages; $linkID++ ) {
                if( $linkID == 1 ) echo "<a href='?page=$linkID'>$linkID</a>";
                else echo " | <a href='?page=$linkID'>$linkID</a>";
            }
            echo "<br>";
        }
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

function showCommentPage( $id ) {
    global $myDB,$myID;
    $id = addslashes( $id );
    echo '<br><hr><br>';
    if( $row = $myDB->query( "SELECT * FROM  `files` WHERE id='$id' AND comment='1'" ) AND $row != 1 ) {
        //print_r( $row );
        echo "<table border='1' width='600px' cellspacing='0' cellpadding='4'>".
        "<tr align='center'><td>Название</td><td>".$row[0]['filename']."</td></tr>".
        "<tr align='center'><td>Дата загрузки</td><td>".$row[0]['date']."</td></tr>".
        "<tr align='center'><td>Загрузил</td><td>".getNameById($row[0]['userid'])."</td></tr>".
        //"<tr align='center'><td>User-Agent</td><td>".$row[0]['ua']."</td></tr>".
        //"<tr align='center'><td>IP</td><td>".$row[0]['ip']."</td></tr>".
        "</table><br>";
        
        if( !$myID ) $author = 'guest';
        else $author = getNameById($myID);
        
        if( $comments = $myDB->query( "SELECT * FROM  `comments` WHERE softid='".$row[0]['id']."' ORDER BY date DESC" ) AND $comments != 1 ) {
            echo "<br><table border='0' width='400px' cellspacing='0' cellpadding='3'>";
            foreach( $comments as $comment ) {
                echo '<tr align="center"><td>'.$comment['date'].'</td><td><i>Автор: </i>'.getNameById($comment['userid']).'</td></tr>'.
                '<tr align="justify"><td colspan="2">'.$comment['message'].'</td></tr>'.
                '<tr><td colspan="2"><br>&nbsp<br></td></tr>';
            }
            echo '</table>';
        } else {
            echo "<br><br><font color='red'>Комментарии пока что никто не писал</font><br>";
        }
        echo "<br><br><form method='post' name='commentForm' action='?comment=$id'><table><tr><td><input type='text' class='text' value='$author' disabled><input type='hidden' name='author' value='$author'><input type='hidden' name='softid' value='".$row[0]['id']."'></td></tr><tr><td><textarea name='mess' class='textarea'></textarea></td></tr></table><br><div class='bigbut' id='go'><a href='#' onclick='document.commentForm.submit();'>Написать</a></div> </form>";
    } else {
        echo '<div id="incorrect">У вас нет прав комментировать данный файл</div><br><br><a href="'.$_SERVER['PHP_SELF'].'">Назад</a>';
    }
}

function getNameById( $userid ) {
    global $myDB;
    if( $row = $myDB->query( "SELECT mail FROM  `users` WHERE id='$userid'" ) AND $row != 1 ) {
        return $row[0]['mail'];
    } else {
        return 'unknown';
    }
}

?>