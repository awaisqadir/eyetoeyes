<?php
$appid          = '356357541147608'; //facebook app id
$appsecret      = '9c09ad0c69fa8959fc98ed09f569a918'; //facebook app secret
$scriptPath         = 'http://awais.comoj.com/mem_friend/test1.php'; //path to script
$friendsPerPage     = 20; //number of friends per page

if (!class_exists('FacebookApiException')) {
    require_once('facebook.php' ); //include facebook api
}
        $facebook = new Facebook(array(
          'appId' => $appid,
          'secret' => $appsecret,
        ));

        $fbuser = $facebook->getUser();
        if ($fbuser) {
          try {
            $access_token = $facebook->getAccessToken();
            $me = $facebook->api('/me');
            } catch (FacebookApiException $e) {
            die($e->getMessage());

          }
        }

        // Show login form if fresh login requires
        if (!$fbuser){
         $loginUrl = $facebook->getLoginUrl(array('redirect_uri'=>$scriptPath));
         die('<a href="'.$loginUrl.'"><string>Login</strong></a>');
        }

        $offsetValue='';
        $previousurl='';
        $nexturl='';

        if(isset($_GET["offset"]) && is_numeric($_GET["offset"]))
        {
            $offsetValue='&offset='.$_GET["offset"];
        }

        $friends = $facebook->api('/me/friends?limit='.$friendsPerPage.$offsetValue);

        if(isset($friends["paging"]["next"]))
        {
            $nexturlOffset = returnFbOffset($friends["paging"]["next"]);
            $nexturl = '<a href="?offset='.$nexturlOffset.'">Next &raquo;</a>';
        }
        if(isset($friends["paging"]["previous"]))
        {
            $previousurlOffset = returnFbOffset($friends["paging"]["previous"]);
            $previousurl = '<a href="?offset='.$previousurlOffset.'">&laquo; Previous</a>';
        }
?>
<html>
<head>
<title>Friend List</title>
<link href="style.css" rel="stylesheet" type="text/css" />
</head>
<body>
<?php
    echo '<div style="padding:20px" align="center">'.$previousurl.' | '.$nexturl.'</div>';
    echo '<ul class="fbFriendLists">';
        foreach ($friends["data"] as $friend) {
            echo '<li class="fbFriendListInline">';
            echo '<img height="50" width="50" src="https://graph.facebook.com/' . $friend["id"] . '/picture"/>';
            echo '<div class="friendname">'.$friend["name"].'</div>';
            echo '</li>';
        }
        echo '</ul>';
        echo '<div style="clear:both"></div>';
        echo '<div style="padding:20px" align="center">'.$previousurl.' | '.$nexturl.'</div>';

function returnFbOffset($url)
{
    $str = parse_url($url);
    parse_str($str["query"], $strarray);
    return $strarray["offset"];
}

?>
</body>
</html>