<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require('simple_html_dom.php');
//CONNECT TO SQL        //
$mysqli = new mysqli("localhost", "root", "root", "dkings");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
//END SQL CONNECTION   //

$sqlSelect = "SELECT a.* FROM dk_main a LEFT JOIN players b on a.name = b.player_name
         WHERE b.player_name IS NULL AND a.position like '%P%'";

//Grab record count
$sql0 = "SELECT count(*) AS rec_count FROM ($sqlSelect) a";
$res = $mysqli->query($sql0);
$res->data_seek(0);
while ($row = $res->fetch_assoc()) {
$rec_count = $row['rec_count'];
}

//Grab one unmatched player
$step = 0;
for ($y = 0; $y < $rec_count;) {
$sql1 = "$sqlSelect LIMIT 1";
$res = $mysqli->query($sql1);
$res->data_seek(0);
while ($row = $res->fetch_assoc()) {
  $unmatchedPlayer = $row['name'];
  $playerID        = $row['player_id'];
}

//Get player name ready for URL
$encodedPlayer = urlencode($unmatchedPlayer);
$hrefPlayer = strtolower(str_replace(' ', '\-', $unmatchedPlayer));

//Grab HTML page used to grep ESPN number
$html = file_get_html('https://www.google.com/search?safe=off&site=&source=hp&q='.$encodedPlayer.'+espn+mlb');

//Test to see if page has player name; if so echo ESPN number.
$bigDivs = $html->find('h3.r');
foreach($bigDivs as $div) {
    $link = $div->find('a');
    $href = $link[0]->href;
    $pattern = '#(?<=id/)[^/'.$hrefPlayer.']+#';
    preg_match($pattern,$href, $espnID);
    echo $espnID[0];
    if ($espnID[0] !== NULL){
      //Insert Player Name and ESPN ID into players table
      $sql0 = "INSERT INTO `players` (`player_name`, `espn_id`, `changed_on`) VALUES ('$unmatchedPlayer', '$espnID[0]', curdate())";
      echo $sql0;
      $mysqli->query($sql0);
      break;
    }
}
$y++;
}
?>
