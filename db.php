<?php
$mysqli = new mysqli("localhost", "root", "", "sonifydb");
if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
}
/*super-basic boilerplate connection code*/

/* just testing things out right now...*/
if (!$mysqli->query("DROP TABLE IF EXISTS ts_songs") ||
    !$mysqli->query("CREATE TABLE ts_songs(id INT, songtitle VARCHAR(64), score  DECIMAL(4,2))") ||
    !$mysqli->query("INSERT INTO ts_songs(id, songtitle, score) VALUES (1, 'Picture to Burn', 7.3 ), (2, 'We Are Never Ever Getting Back Together', 10.3 ), (3, 'Blank Space', 12.25)")) {
    echo "Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error;
} /* above creates and populates a table with three songs and their mood scores */

/* Below accesses that table and spits out data from each row, accessed by key */
$res = $mysqli->query("SELECT id, songtitle, score FROM ts_songs ORDER BY id ASC");

echo "Reverse order...\n<ul>";
for ($row_no = $res->num_rows - 1; $row_no >= 0; $row_no--) {
    $res->data_seek($row_no);
    $row = $res->fetch_assoc();
    echo "<li> id = " . $row['id'] . "\n song = " . $row['songtitle'] . "\n value = " . $row['score'] . "\n</li>";
}

echo "</ul>Result set order...\n<ul>";
$res->data_seek(0);
while ($row = $res->fetch_assoc()) {
   echo "<li> id = " . $row['id'] . "\n song = " . $row['songtitle'] . "\n value = " . $row['score'] . "\n</li>";
}
echo "</ul>";
?>