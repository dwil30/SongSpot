<?php
session_start();
$ItemID = $_POST['SongID'];
$array = explode(",", $ItemID);
foreach ($array as $value){
    for ($y=0;$y<=count($_SESSION['Found']);$y++){
        if(isset($_SESSION['Found'][$y][2]) && ($_SESSION['Found'][$y][2] == $value)){
        unset($_SESSION['Found'][$y]);
        }
    }
}
echo 'Success';
?>  
