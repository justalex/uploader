<!DOCTYPE html> 
<html> 
    <head> 
        <meta charset="UTF-8"> 
        <link rel="stylesheet" type="text/css" href="css/style.css" /> 
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"></script> 
        <title>Установка Onliner Uploader</title> 
    </head> 
    <body> 
    
<script> 
$(function (){ 
    $('#setup').click(function() {
        var host = $('#host').val();
        var user = $('#user').val();
        var pass = $('#pass').val();
        var base = $('#base').val();
        var show = $('#show').val();
        
        if( host && user && base && show ) {
            $("#ajaxstatus").html('<img src="images/loading.gif" border="0"> <span style="vertical-align: super;"><b>Loading ...</b></span>');
            var data = 'host='+host+'&user='+user+'&pass='+pass+'&base='+base+'&show='+show;
            $.ajax({  
                type: 'post',  
                url: 'ajax.php', 
                data: data,  
                success: function(get){
                    $("#ajaxstatus").html('');
                    $('#step-one').slideToggle('slow');
                    if(get=='done') {
                        $('#step-two').slideToggle('slow');
                    } else {
                        $('#step-zero').slideToggle('slow');
                        $("#more").html(' [ '+get+' ] ');
                    }
                }
            });
        }
    });
    
    $('#enjoy').click(function() {
        $('#step-two').slideToggle('slow');
        window.location.replace("<?php echo dirname( $_SERVER['PHP_SELF'] ).'/index.php'; ?>");
    });
    
    $('#return').click(function() {
        $('#step-zero').slideToggle('slow');
        $('#step-one').slideToggle('slow');
    });
});
</script> 

<div id='ajaxstatus'></div><br>
<div id='step-one' style="display:block">
    <table align='center'>
        <tr><td><input type='text' id='host' class='text' value='localhost'></td><td> MySQL HOST </td></tr>
        <tr><td><input type='text' id='user' class='text' value='root'></td><td> MySQL USER </td></tr>
        <tr><td><input type='password' id='pass' class='text' value=''></td><td> MySQL PASSWORD </td></tr>
        <tr><td><input type='text' id='base' class='text' value='uploader'></td><td> MySQL BASE </td></tr>
        <tr><td>&nbsp</td></tr>
        <tr><td><input type='text' id='show' class='text' value='25'></td><td> Max Files On Page </td></tr>
    </table>
    <br>
    <div class='bigbut' id='setup'><a href='#' id='setup'>Setup</a></div>
</div>

<div id='step-two' style="display:none">
    <div id='correct'>Установка успешно завершена!</div>
    <br><br>
    <a href='#' id='enjoy'>Enjoy</a>
</div>

<div id='step-zero' style="display:none">
    <div id='incorrect'>Во время установки произошёл сбой! <span id='more'></span></div>
    <br><br>
    <a href='#' id='return'>Вернитесь и повторите попытку</a>
</div>

    </body>
</html>