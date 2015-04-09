<?php
session_start();
ini_set("max_execution_time", "600"); 
set_time_limit(600);
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
<html data-wf-site="55076b2b51c487ac7e1745db" data-wf-page="55076b2b51c487ac7e1745dd">
<head>
  <meta charset="utf-8">
  <title>Seo title here</title>
  <meta name="description" content="seo description here">
  <meta name="keywords" content="keywords">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="generator" content="Webflow">
  <link rel="stylesheet" type="text/css" href="css/normalize.css">
  <link rel="stylesheet" type="text/css" href="css/webflow.css">
  <link rel="stylesheet" type="text/css" href="css/rdioapp.webflow.css">
  <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.4.7/webfont.js"></script>
  <script>
    WebFont.load({
      google: {
        families: ["Open Sans:300,300italic,400,400italic,600,600italic,700,700italic,800,800italic]
      }
    });
  </script>
  <script type="text/javascript" src="js/modernizr.js"></script>
  <link rel="shortcut icon" type="image/x-icon" href="https://y7v4p6k4.ssl.hwcdn.net/placeholder/favicon.ico">
  <link rel="apple-touch-icon" href="https://daks2k3a4ib2z.cloudfront.net/img/webclip.png">
</head>
<body>
  <div class="w-section header-section main-page">
    <div class="w-container">
      <div class="content-wrapper">
        <h1>Song Spot</h1>
        <p class="subtitle">Create a playlist in Rdio by uploading a CSV&nbsp;with tracks and artists</p>
          <?php echo isset($_SESSION['errors']) ? '<div class="subtitle errors">'.$_SESSION['errors'].'</div>': '';?>
          <form id="upload" action="rdio-create-playlist.php" method="post" enctype="multipart/form-data">
              <input type="text" value="<?php echo uniqid(); ?>" style="display:none;">
            <div class="left"><input type="file" name="myFile" required><br><br>  <form class="w-clearfix" name="wf-form-signup-form" data-name="Signup Form">
            </div><br>
                <input class="w-input field" id="field" type="text" placeholder="Enter a name for your playlist..." name="name" required="required">
            <input class="w-button button" name="submit" type="submit" value="Upload CSV">
            <div class="w-button button button2" style="display:none;"><img id="loading-image" src="images/ajax-loader.gif" alt="Loading..." /></div><br>
            <progress id='progressor' value="0" max='100' style="display:none;"></progress>  
            <span id="percentage" style="text-align:left; display:block; margin-top:5px;"></span>    
          </form>
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
                <a href="?logout=1">Log out.</a>
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
    <script type="text/javascript">
    $('#upload').submit(function() {
        $('.button').hide();
        $('.button2').show(); 
    });
    </script>
  <!--[if lte IE 9]><script src="https://cdnjs.cloudflare.com/ajax/libs/placeholders/3.0.2/placeholders.min.js"></script><![endif]-->
</body>
</html>