<?php 
@session_start();
if(strlen($_SESSION['login'])>0){
    echo "Welcome ".$_SESSION['login'];
    echo "<br><a href='logout.php'>Logout</a>";
}else{
    header("location:index.php");
};
?>