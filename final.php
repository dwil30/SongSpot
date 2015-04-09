<?php 
session_start();

    //Begin RDIO Code
    require_once 'rdio.php';
    require_once 'rdio-consumer-credentials.php';

    //Create an instance of the Rdio object with our consumer credentials
    $rdio = new Rdio(array(RDIO_CONSUMER_KEY, RDIO_CONSUMER_SECRET));

    //Determine current URL
    if ($_SERVER['SERVER_NAME'] == 'localhost'){$add = ':8888';}
    $current_url = "http" . ((!empty($_SERVER['HTTPS'])) ? "s" : "") .
    "://" . $_SERVER['SERVER_NAME'].$add.$_SERVER['SCRIPT_NAME'];

    //Log out, just throw away the session data and return to current URL
    if ($_GET['logout']) {
        session_destroy();
        header('Location: '.$current_url);
    }
    
    //If we have a token in our session, use it
    if ($_SESSION['oauth_token'] && $_SESSION['oauth_token_secret']) {
        $rdio->token = array($_SESSION['oauth_token'],
        $_SESSION['oauth_token_secret']);
        if ($_GET['oauth_verifier']) {
            # we've been passed a verifier, that means that we're in the middle of authentication.
            $rdio->complete_authentication($_GET['oauth_verifier']);
            # save the new token in our session
            $_SESSION['oauth_token'] = $rdio->token[0];
            $_SESSION['oauth_token_secret'] = $rdio->token[1];
        }
        //create list of songs
        foreach($_SESSION['Found'] as $value){$songs .= $value[2].",";}
        $currentUser = $rdio->call('currentUser');
        if ($currentUser){
            $playlist = $rdio->call('createPlaylist', array("name" => $_SESSION['name'],"description" => "Playlist created using the Song Spot App","tracks" => $songs))->result;

            if (empty($playlist)){
                //do something
            }
        }
        else {
                //Auth failure, clear session and start again
                session_destroy();
                header('Location: '.$current_url);
            }
    }
    else {
        # we have no authentication tokens.
        # ask the user to approve this app
        $authorize_url = $rdio->begin_authentication($current_url);
        # save the new token in our session
        $_SESSION['oauth_token'] = $rdio->token[0];
        $_SESSION['oauth_token_secret'] = $rdio->token[1];
        header('Location: '.$authorize_url);
    }  
?>
<!DOCTYPE html>
<html data-wf-site="55076b2b51c487ac7e1745db" data-wf-page="5508200262cbab2574bceacb">
<head>
  <meta charset="utf-8">
  <title>Final</title>
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
  <div class="w-section header-section final">
    <div class="w-container search-container">
      <h2>Playlist&nbsp;INFO</h2>
      <div>
        <ul class="w-list-unstyled">
          <li class="list-info"><img src="<?php echo $playlist->icon;?>"></li>    
          <li class="list-info"><strong>Playlist name:&nbsp;</strong><?php echo $playlist->name;?></li>
          <li class="list-info"><strong>Short URL: </strong><?php echo '<a href="'.$playlist->shortUrl.'">'.$playlist->shortUrl.'</a>';?></li>
             <li class="list-info"><strong>Embed URL: </strong><?php echo '<a href="'.$playlist->embedUrl.'">'.$playlist->embedUrl.'</a>';?></li>
          <li class="list-info"><strong>Number of Tracks Added:</strong>&nbsp;<?php echo $playlist->length;?></li>    
        </ul>
      </div>
        <br><center><a class="list-info" href="index.php">Create New Playlist</a></center>
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
  <script type="text/javascript" src="js/webflow.js"></script>
  <!--[if lte IE 9]><script src="https://cdnjs.cloudflare.com/ajax/libs/placeholders/3.0.2/placeholders.min.js"></script><![endif]-->
</body>
</html>