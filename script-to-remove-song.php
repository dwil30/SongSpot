<?php
session_start();
$ItemID = $_POST['ID'];
for ($y=0;$y<count($_SESSION['Found']);$y++){
    if(isset($_SESSION['Found'][$y][2]) && ($_SESSION['Found'][$y][2] == $ItemID)){
    unset($_SESSION['Found'][$y]);
    }
}
echo 'Success';
?>  
