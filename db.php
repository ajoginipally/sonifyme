<?php
/*super-basic boilerplate connection code*/
$mysqli = new mysqli("localhost", "root", "", "sonifydb");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}

/* just testing things out right now...create and populate a table with three songs and their mood scores 
/*
if (!$mysqli->query("DROP TABLE IF EXISTS ts_songs") ||
    !$mysqli->query("CREATE TABLE ts_songs(id INT, songtitle VARCHAR(64), score  DECIMAL(4,2))") ||
    !$mysqli->query("INSERT INTO ts_songs(id, songtitle, score) VALUES (1, 'We Are Never Ever Getting Back Together', 10.3), (2, 'Picture to Burn', 7.3  ), (3, 'Blank Space', 12.25)")) {
    echo "Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error;
} 
*/

/* Access that table and spit out data from each row, accessed by key(column) */
$res = $mysqli->query("SELECT id, songtitle, score FROM ts_songs ORDER BY score ASC");

/*echo "Reverse order...\n<ul>";
for ($row_no = $res->num_rows - 1; $row_no >= 0; $row_no--) {
   $res->data_seek($row_no);
   $row = $res->fetch_assoc();
   echo "<li>" . $row['songtitle'] . "\n   <strong>" . $row['score'] . "</strong> \n</li>";
}
echo "</ul>"*/
echo "Result set order...\n<ul>";
$res->data_seek(0);
while ($row = $res->fetch_assoc()) {
   echo "<li>" . $row['songtitle'] . "\n   <strong> " . $row['score'] . "</strong>\n</li>";
}
echo "</ul>";

$bResAll = $mysqli->query("SELECT id, songtitle, score FROM ts_songs WHERE isBreakup = 'TRUE' ORDER BY score ASC");
echo "Breakup set order...\n<ul>";
$bResAll->data_seek(0);
while ($row = $bResAll->fetch_assoc()) {
   echo "<li>" . $row['songtitle'] . "\n   <strong> " . $row['score'] . "</strong>\n</li>";
}
echo "</ul>";
$val = rand(1, 25);
/* found closest result solution here 6/9/2015:
http://stackoverflow.com/questions/592209/find-closest-numeric-value-in-database
*/
$playRes = $mysqli->query("SELECT * FROM ts_songs ORDER BY ABS(score - $val) LIMIT 5");
echo "Your random number: ".$val . "<br>Your Playlist:<ul>";
/*spit out closest five songs to value from random number*/
while ($row = $playRes->fetch_assoc()) {
   echo "<li>" . $row['songtitle'] . "\n   <strong> " . $row['score'] . "</strong>\n</li>";
}
echo "</ul>";
$albRes = $mysqli->query("SELECT * FROM ts_albums ORDER BY ABS(score - $val) LIMIT 1");
$songRes = $mysqli->query("SELECT * FROM ts_songs ORDER BY ABS(score - $val) LIMIT 1");
$row = $songRes->fetch_assoc();
echo "Your song match: ". $row['songtitle']. "\n   <strong> " . $row['score'] . "</strong><br>\n";
$songRes = $mysqli->query("SELECT * FROM ts_songs WHERE isBreakup = 'TRUE' ORDER BY ABS(score - $val) LIMIT 1");
$row = $songRes->fetch_assoc();
echo "Your breakup song match: ". $row['songtitle']. "\n   <strong> " . $row['score'] . "</strong><br>\n";
$row = $albRes->fetch_assoc();
echo "Your Swift Gen match: ". $row['album']. "\n   <strong> " . $row['score'] . "</strong>\n";
?>