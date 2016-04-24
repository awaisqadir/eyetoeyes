<?php	
	session_start();
	
	// facebook code for getting friend list
	$appid          = '356357541147608'; //facebook app id
$appsecret      = '9c09ad0c69fa8959fc98ed09f569a918'; //facebook app secret
$scriptPath         = 'http://awais.comoj.com/mem_friend/'; //path to script
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
		
		

        $friends = $facebook->api('me/friends/?limit=0');
		shuffle($friends["data"]);

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
		
		function returnFbOffset($url)
{
    $str = parse_url($url);
    parse_str($str["query"], $strarray);
    return $strarray["offset"];
}

		// facebook code for getting friend list
		
	$p = 0;	
		
		
		
		
	// first check if we have a utility function
	if ( isset($_REQUEST["won"]) ){
		// if we won we need to reset the game board
		unset($_SESSION['board']);
		$_SESSION['games_won'] = ++$_SESSION['games_won'];
		$response = array("status" => "ok");
		exit(json_encode($response));
	}
	
	// $CARDS = array();
	

	
	function fill_dropdown($ind) {
		switch ($ind)
		{
			case 1:
 				return "Beginner";
  			break;
			case 2:
 				return "Elementary"; 
  			break;
			case 3:
  				return "Intermediate";
  			break;
			case 4:
 				return "Advanced"; 
  			break;
			case 5:
  				return "Expert";
  			break;
			case 6:
 				return "Professional"; 
  			break;
 				return "Beginner";						
			default:
 
		} 

			
	}
		
	// All the card files we have
	foreach ($friends["data"] as $friend)
	{
		//	echo "https://graph.facebook.com/" . $friend["id"] . "/picture";
		//	echo "\n";
	
		//	array_push($CARDS, "https://graph.facebook.com/' . $friend["id"] . '/picture");
	
		$CARDS_1[$p] = "https://graph.facebook.com/" . $friend["id"] . "/picture?width=150&height=150";
		$p=$p+1;
		if($p >= 20 )
		{
			break;
		}
				
	}
//	print_r( $CARDS_1);
	class Board
	{
		private $css = array();
		private $hi = array();
		private $cards = array();
		private $cards_names = array();
		private $cols = 0;
		private $rows = 0;
		private $modes = array(6, 8, 10, 12, 15, 18);
		
		function __construct($level, $card_files) {
			$num_of_cards = $this->modes[$level - 1];
			
			// Shuffle the cards available so we won't pick the 
			// same ones every time
			shuffle($card_files);
			// Get the card objects
			$cards = array();
			for ( $i = 0; $i < $num_of_cards; ++$i ){
				$cards[$i] = new Card($card_files[$i]);
				$this->css[] = $cards[$i]->get_css_block();
				$this->hi[] = $cards[$i]->get_hidden_image();
			}
			// Double the array so we will have pairs
			$this->cards = array_merge($cards, $cards);
			//print_r( $this->cards);
			// Shuffle the cards to create the order on the board
			shuffle($this->cards);
			
			// Get the number of cols
			// echo count($this->cards);
			$num = count($this->cards);
			$sr = sqrt($num);
			$this->rows = floor($sr);
			while ( $num % $this->rows ){
				--$this->rows;
			}
			$this->cols = $num / $this->rows;
		}
		
		function max_level(){
			return count($this->modes);
		}
		
		function get_css(){
			return implode("\n",$this->css);
		}
		
		function get_images(){
			return implode("\n",$this->hi);
		}
		
		function debug_print(){
			$p_rslt = array("cards"=>$this->cards, "rows"=>$this->rows, "cols"=>$this->cols);
			print "<br/ >".json_encode($p_rslt);
		}
		
		function get_rows(){
			return $this->rows;
		}
		
		function get_cols(){
			return $this->cols;
		}
		
		function get_cards(){
			return $this->cards;
		}
		
		
		function get_size(){
			return count($this->cards);
		}
		
		function get_card($index){
			return $this->cards[$index];
		}
		
		function get_html(){
			// For each card
			for ( $ii = 0 ; $ii < $this->get_size() ; ++$ii ){
				// Check if it's time for a new row
				if ( ($ii % $this->get_cols()) == 0 ){
					print "\r<div class=\"clear\"></div>";
				}
				print $this->get_card($ii)->get_html_block();
			}
		}
	}

	class Card{	
		private $css_class = "";
		private $url = "";
		
		function __construct($url) {
			$this->url = $url;
			$this->css_class = $this->extract_name($url);
			
		}
		
		function get_name(){
			return $this->css_class;
		}
		
		function get_css_block(){
		// echo "\n.".$this->get_name()."{background-image:url('".$this->url."'); center center no-repeat;}";
		//	return "\n.".$this->get_name()."{ background-image:url(".$this->url."); background: center center no-repeat;}";
		return "\n.".$this->get_name()."{background:url(".$this->url.") center center no-repeat; background-size: 100%;}";
		}
		
		function get_hidden_image(){
		// echo "\n.".$this->get_name()."{background-image:url('".$this->url."'); center center no-repeat;}";
		//	return "\n.".$this->get_name()."{ background-image:url(".$this->url."); background: center center no-repeat;}";
		return "<img src='".$this->url."' style='display: none'>";
		}
		
		function get_html_simple_block(){
			return "\r<div class=\"card {toggle:'".$this->get_name()."'}\"></div>";
		}
		
		function get_html_block(){
			return "\r<div class=\"card {toggle:'".$this->get_name()."'}\">
						\r<div class=\"off\"></div>
						\r<div class=\"on\"></div>
					</div>";
		}
		private function extract_name($str){
			$tmp = pathinfo($str);
			// echo $tmp['filename']
			return "img".substr($str,27,-29);
		}
	}

	$level = 1;
	if (!isset($_SESSION['games_won'])) {
		$_SESSION['games_won'] = 0;
	}
	
	if (isset($_REQUEST['level']) ) {
		$level = $_REQUEST['level'];
		
		$board = new Board($level, $CARDS_1);
		$_SESSION['board'] = $board;
	} else {
		if (!isset($_SESSION['board'])) {
			$board = new Board($level, $CARDS_1);
			$_SESSION['board'] = $board;
		} else {
			$board = $_SESSION['board'];
		}
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" 
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" >
<head>
<title>Memory Friends</title>
<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
<link rel="stylesheet" href="memory_game.css" type="text/css" />
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3/jquery.min.js"></script>
<script type="text/javascript" src="jquery.metadata.js"></script>
<script type="text/javascript" src="jquery.quickflip.js"></script>
<script type="text/javascript" src="memory_game.js"></script>
<script type="text/javascript" src="swfobject.js"></script>
  <script src="http://connect.facebook.net/en_US/all.js"></script>
<script type="text/javascript">
	var flashvars = false;
	var attributes = {};
	var params = {
	  allowscriptaccess : "always",
	  wmode : "transparent",
	  menu: "false"
	};
	swfobject.embedSWF("sfx.swf", "sfx_movie", "1", "1", "9.0.0", "expressInstall.swf", flashvars, params, attributes);
	
	
</script>


</head>
<style>
<?php  print $board->get_css();
 ?>
</style>
<body>
<div id="fb-root"></div>
  
<h1>Memory Friends</h1>
<div id="sfx_movie">
  <h1>This page requires flash for full funcionality</h1>
  <p><a href="http://www.adobe.com/go/getflashplayer"> <img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /> </a></p>
</div>
<div id="control" style="width:<?php print $board->get_cols()*155; ?>px; font-size: 16px;">
  <label><strong>Level</strong>:</label>
  <select id="level_chooser">
    <?php 
			print "<!-- ".$board->max_level()." -->";
			for ( $i = 0; $i < $board->max_level(); ++$i ){
					$selected = ( ($i+1) == $level ) ? " selected=selected" : "";
					print "\r<option value=\"".($i+1)."\"".$selected.">".fill_dropdown($i+1)."</option>";
			}
		?>
  </select>
  <label><strong>Games Finished: </strong></label>
  <span><?php print $_SESSION["games_won"]; ?></span>
  <label><strong>Moves</strong>:</label>
  <span id="num_of_moves">0</span> </div>
<div id="game_board" style="width:<?php print $board->get_cols()*155; ?>px;">
  <?php
	print $board->get_html();
	print $board->get_images();
	

?>
</div>
<div id="player_won"></div>

<div id="start_again"><a id="again" href="" onclick="sendRequestViaMultiFriendSelector();return false;" >Click here to play again</a></div>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>
  <input type="button"
      onclick="sendRequestViaMultiFriendSelector(); return false;"
      value="Tell Other Friends"
    />
</p>
    
<div id="sfx_movie">
  <h1>This page requires flash for full funcionality</h1>
  <p><a href="http://www.adobe.com/go/getflashplayer"> <img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /> </a></p>
</div>

<script>
      FB.init({
        appId  : '356357541147608',
        frictionlessRequests: true
      });

      function sendRequestToRecipients() {
        var user_ids = document.getElementsByName("user_ids")[0].value;
        FB.ui({method: 'apprequests',
          message: 'My Great Request',
          to: user_ids
        }, requestCallback);
      }

      function sendRequestViaMultiFriendSelector() {
        FB.ui({method: 'apprequests',
          message: 'Memory Friends Requests'
        }, requestCallback);
		
		
		
      }
      
      function requestCallback(response) {
        // Handle callback here
		window.location.href = '#';
      }
    </script>
</body>
</html>
