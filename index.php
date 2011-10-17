<?php
 include('top.php');
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title><?=$title?></title>
    <link href="images/favicon.png" rel="icon" type="image/x-png" />
    <style type="text/css">
        <?php
            include("css/dynamic_style.php");
        ?>
    </style>
    <script type="text/javascript" src="apps/jquery-1.6.4.min.js"></script>
    <script type="text/javascript" src="apps/jquery.localscroll-1.2.7-min.js"></script>
    <script type="text/javascript" src="apps/jquery.scrollTo-min.js"></script>
    <script type="text/javascript" src="js/js_init.php"></script>
    <script type="text/javascript" src="js/js_polling.js"></script>
    <script type="text/javascript" src="js/js_core.js"></script>

    <script type="text/javascript">
        <?php
           
             if($_GET['newbie']==1){

                $u = $_SESSION['newbie_username'];
                $p = $_SESSION['newbie_password'];
                print "$(document).ready(function(){";
                print "$('#login_password').attr('value','$p');";
                print "$('#login_username').attr('value','$u');";

                print "$('#login_form').submit();";
                print "});";

             }

        ?>

    </script>
</head>

<body>

<?php 
    if($_SESSION['session_loggedIn']!=$uniqueID){
        $front = new Document($front);
        $front->outputAndEval();

?>

</div><h2>Login</h2><div id="login">

<?php
   
        include('user/login.php');
        
?>

</div><h2>Register new user</h2><div id="new_user">

<?

        include("user/new_user.php");

?>
</div>
<?

    }else{ 
?>

    <div id="command_line">
    <input id="chatmsg"> 
    <input id="lastmsg" type="hidden">
    <div id="aspect_sets">
    <?php
        $aspsets_doc = new Document($aspsets);
        $aspsets_doc->outputAndEval();
    ?>
    </div>
    </div>
    <div id="all_aspects"></div>
    <?php
  
 } 
?>


</body>

</html>
