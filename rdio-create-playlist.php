<?php
session_start();
ini_set("max_execution_time", "600"); 
set_time_limit(600);
if(isset($_POST['submit'])){
    
    //clear error variable
    unset($_SESSION['errors']); 

    //validate uploaded file
    if (!empty($_FILES["myFile"])) {
    
        $myFile = $_FILES["myFile"];
 
        if ($myFile["error"] !== UPLOAD_ERR_OK) {
            $_SESSION['errors'] .= "<p>An error occurred while uploading the file. Please try again</p>";
            header("location:index.php");
        }
        else {
            // ensure a safe filename
            $name = preg_replace("/[^A-Z0-9._-]/i", "_", $myFile["name"]);
   
             // verify the file is a CSV
            $mimes = array('text/csv');
            if(!in_array($_FILES['myFile']['type'],$mimes)){
                $_SESSION['errors'] .=  "<p>File is not a CSV. Please try again.</p>";
                header("location:index.php");
                exit;
                
            // virus scan file (not needed)
                
            // move file to another directory on the server (not needed)    
                
            }
        }
    }
    
    else {
        $_SESSION['errors'] .=  "<p>File was empty. Please try again</p>";
        header("location:index.php");
        exit;
    }
  
    //parse CSV into an array
    $row=0;
    ini_set('auto_detect_line_endings',TRUE);
    if (($handle = fopen($myFile["tmp_name"], "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000,',','"')) !== FALSE) {
            if (array(null) !== $data) {
            $import[$row] = array((trim($data[0])),trim($data[1]));
            $row++;}
        }
        fclose($handle);
    }
    
    //strip array of bad characters and remove blank rows
    foreach ($import as $key=> $array){
        foreach ($array as $key2=>$value){
            if(empty($import[$key][$key2])){unset($import[$key]);}
            else{
            $import[$key][$key2] = str_replace('"','',$import[$key][$key2]);
            $import[$key][$key2] = str_replace('“','',$import[$key][$key2]);
            $import[$key][$key2] = str_replace('”','',$import[$key][$key2]);
            $import[$key][$key2] = str_replace('&','and',$import[$key][$key2]);
            }
        }
    }
        
    //Begin RDIO Code
    require_once 'rdio.php';
    require_once 'rdio-consumer-credentials.php';

    //Create an instance of the Rdio object with our consumer credentials
    $rdio = new Rdio(array(RDIO_CONSUMER_KEY, RDIO_CONSUMER_SECRET));
    
    $_SESSION['notFound'] = $_SESSION['Found'] = array(); 
    $_SESSION['name'] = $_POST['name'];
    
    //perform search on artist name
    foreach ($import as $item){
        $artist_search = $rdio->call('searchSuggestions', array("query" => $item[1],"types" => "artist","extras" => "-*,key", "count" => "1"))->result;
       
        //query on artist produced no results
        if (empty($artist_search[0])){
            array_push($_SESSION['notFound'], array($item[1],$item[0],'<span class="artist">Artist not found</span>'));          
        }
        else {
            $artist = $artist_search[0]->key;
            $track = $rdio->call('getTracksForArtist', array("artist" => $artist, "extras" => "-*,name,key,artist", "count" => "1", "query" => $item[0]))->result;
            //if results are returned match on Artist name
            if($track){ //track found
                array_push($_SESSION['Found'],array($track[0]->artist,$track[0]->name,$track[0]->key));  
            }
            else {
                $short = substr($item[0], 0, 4);
                $track2 = $rdio->call('getTracksForArtist', array("artist" => $artist, "extras" => "-*,name,key,artist", "count" => "1", "query" => $short))->result;
                if($track2){ //track found
                array_push($_SESSION['Found'],array($track2[0]->artist,$track2[0]->name,$track2[0]->key));  
                }
                else{
                 array_push($_SESSION['notFound'], array($item[1],$item[0],'<span class="song">Song not found</span>'));
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html data-wf-site="55076b2b51c487ac7e1745db" data-wf-page="550772f962cbab2574bccf85">
<head>
  <meta charset="utf-8">
  <title>Upload Results</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="generator" content="Webflow">
  <link rel="stylesheet" type="text/css" href="css/normalize.css">
  <link rel="stylesheet" type="text/css" href="css/webflow.css">
  <link rel="stylesheet" type="text/css" href="css/rdioapp.webflow.css">
  <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.4.7/webfont.js"></script>
  <script>
    WebFont.load({
      google: {
        families: ["Open Sans:300,300italic,400,400italic,600,600italic,700,700italic,800,800italic","Acme:regular"]
      }
    });
  </script>
  <script type="text/javascript" src="js/modernizr.js"></script>
  <link rel="shortcut icon" type="image/x-icon" href="https://y7v4p6k4.ssl.hwcdn.net/placeholder/favicon.ico">
  <link rel="apple-touch-icon" href="https://daks2k3a4ib2z.cloudfront.net/img/webclip.png">
</head>
<body>
  <div class="w-section header-section auto-height">
    <div class="w-container search-container">
      <h2>Tracks Found</h2>
        <form id="create" action="final.php" method="post">
            <div id="noTracks" class="label" style="display:none;">There are no tracks in the playlist</div>
            <?php
            if (count($_SESSION['Found']) < 1){echo '<div class="label">There are no tracks in the playlist</div>';}
            else {
             
            foreach ($_SESSION['Found'] as $value){$i=$value[2];?>
            <div class="song-container <?=$i?>">
                <div class="w-checkbox w-clearfix">
                    <input class="w-checkbox-input checkbox" value="<?=$i?>" id="<?=$i?>" type="checkbox" onclick="Validate()" name="checkbox-<?=$i?>"><label id="<?=$i?>" class="w-form-label label" for="<?=$i?>"><?=$value[1]?> - <?=$value[0]?></label>
                </div>
            <div class="pic-block"><a href="#" class="pic" id="<?=$i?>" onclick="removeItem('<?=$i?>')"><img src="images/WhiteX.png" alt="55080eeb3f049b247410e483_WhiteX.png"></a>
            </div>
          </div>
            <?php }
            echo '<div id="subheader">Check song and select "remove" to clear from playlist</div>'; }?>
        <div class="div-line"></div>
          <div class="w-clearfix bottom-text">
            <div class="left">   
              <div class="total"><strong><div id="totalCount"></div></strong>&nbsp;songs total</div>
            </div>
            <div class="clear">
             <a id="clearlink" class="total link" href="#" onclick="removeBox()"><strong><div class="totalClear"></div></strong>&nbsp;selected - Remove from playlist</a>
            </div>
          </div>
          <a href="final.php"><input class="w-button submit-button" type="submit" value="Create Playlist" data-wait="Please wait..."></a>
          <div class="w-button submit-button submit-button2" style="display:none;"><img id="loading-image2" src="images/ajax-loader.gif" alt="Loading..." /></div>    
        </form>
        <a href="index.php" class="w-button submit-button" style="float:right;text-align:center;text-decoration:none;background-color:grey;" value="Start Over">Start Over</a>
    </div>
    <div class="w-container search-container">
      <h2>Tracks Skipped</h2>
      <div>
          <?php
            if (count($_SESSION['notFound']) < 1){echo '<div class="label">There were no skipped tracks in the playlist</div>';}
            else {
            echo '<ul class="w-list-unstyled">';    
            foreach ($_SESSION['notFound'] as $value){ ?>
                <li class="skipped"><?=$value[1]?> - <?=$value[0]?> - <span class="skipped-reason"><?=$value[2]?></span></li>
          <?php }} ?>
            </ul>
        <div class="div-line"></div>
        <div class="w-clearfix bottom-text">
          <div class="left">
            <div class="total"><strong><div id="totalSkipped"></div></strong>&nbsp;songs skipped</div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="w-section footer-section">
    <div class="w-container">
      <div class="w-row">
        <div class="w-col w-col-6 w-col-small-6">
          <div class="w-clearfix social-widget-wrapper">
            <div class="w-widget w-widget-twitter social-widget">
              <!--<iframe src="https://platform.twitter.com/widgets/tweet_button.html#url=http%3A%2F%2Fwebflow.com&amp;counturl=webflow.com&amp;text=Check%20out%20this%20site&amp;count=horizontal&amp;size=m&amp;dnt=true" scrolling="no" frameborder="0" allowtransparency="true" style="border: none; overflow: hidden; width: 110px; height: 20px;"></iframe>-->
            </div>
            <div class="w-widget w-widget-facebook social-widget">
              <!--<iframe src="https://www.facebook.com/plugins/like.php?href=https%3A%2F%2Ffacebook.com%2Fwebflow&amp;layout=button_count&amp;locale=en_US&amp;action=like&amp;show_faces=false&amp;share=false" scrolling="no" frameborder="0" allowtransparency="true" style="border: none; overflow: hidden; width: 90px; height: 20px;"></iframe>-->
            </div>
          </div>
        </div>
        <div class="w-col w-col-6 w-col-small-6">
          <div class="copyright">© 2015 Song Spot App. All right reserved.&nbsp;</div>
        </div>
      </div>
    </div>
  </div>
  <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
<script type="text/javascript">  
function Validate(){
    var numberOfChecked = $('input:checkbox:checked').length;
    if (numberOfChecked > 0){
        $('.clear').show();
        $('.totalClear').html(numberOfChecked);
    }
    else {$('.clear').hide();}
}
var count = $('.song-container').length;      
document.querySelector('#totalCount').innerHTML = count;
var skipped = $('.skipped').length;  
document.querySelector('#totalSkipped').innerHTML = skipped;    
    
function removeItem(ID){
    count--;
    document.querySelector('#totalCount').innerHTML = count;
    $.ajax({
        type: "POST",
        url: "script-to-remove-song.php",
        data: {ID: ID},
        success: function(msg){
            $('.'+ID).remove();
        }
    });
}

function removeBox() {
    var checkbox_value = ""; 
    $(":checkbox").each(function () {
        var ischecked = $(this).is(":checked");
        if (ischecked) {
            checkbox_value += $(this).val() + ",";
        }
    });
      $.ajax({
        type: "POST",
        url: "script-to-remove-checkbox.php",
        data: {SongID: checkbox_value},
        success: function(msg){
                var numberOfChecked = $('input:checkbox:checked').length;
                count = count - numberOfChecked;
                if (count == 0){$('#noTracks').show();}
                document.querySelector('#totalCount').innerHTML = count;
                $('.clear').hide();
                $(":checkbox").each(function () {
                    var ischecked = $(this).is(":checked");
                    if (ischecked) {
                       var ID = $(this).attr('ID');
                       $('.song-container.'+ID).remove();
                   }
               });
        }
      });
}
</script>
<script type="text/javascript">
    $('#create').submit(function() {
        $('.submit-button').hide();
        $('.submit-button2').show(); 
    });
    </script>
  <script type="text/javascript" src="js/webflow.js"></script>
  <!--[if lte IE 9]><script src="https://cdnjs.cloudflare.com/ajax/libs/placeholders/3.0.2/placeholders.min.js"></script><![endif]-->
</body>
</html>