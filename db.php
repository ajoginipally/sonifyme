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

   /* Access table and spit out data from each row, accessed by key(column) 
   $res = $mysqli->query("SELECT songtitle, score FROM ts_songs ORDER BY score ASC");
   /*** 
   $res: all songs
   $bResAll: all breakup songs
   $playRes: The 5 rows closest to a given score value 
   $albRes: The closest match album row to a given score value
   $val: a random number from 1 to 25 inclusive
   $rInd: random number from 0 to $songRes->num_rows-1 to select from multiple
      matches
   $songRes: currently used both for single song selection and breakup song 
      selection
   ***/
/*
   echo "All songs:
   $res->data_seek(0);
   while ($row = $res->fetch_assoc()) {
      echo "<li>" . $row['songtitle'] . "\n   <strong> " . $row['score'] . "</strong>\n</li>";
   }
   echo "</ul>";

   $bResAll = $mysqli->query("SELECT songtitle, score FROM ts_songs WHERE isBreakup = 'TRUE' ORDER BY score ASC");
   echo "Breakup songs:\n<ul>";
   $bResAll->data_seek(0);
   while ($row = $bResAll->fetch_assoc()) {
      echo "<li>" . $row['songtitle'] . "\n   <strong> " . $row['score'] . "</strong>\n</li>";
   }
   echo "</ul>";*/
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

   /*for both sonify song and breakup song: cover cases where a) there 
   are more than one exact match b) there are more than one equivalent closest 
   match in addition to c) there is a single closest match.
   There is probably a more efficient and less-verbose way of doing this, but 
   what's here seems to work*/
   $songRes = $mysqli->query("SELECT * FROM ts_songs WHERE score = $val");
   if($songRes->num_rows > 1){ 
   /*if more than one exact match for song*/
      //select one of these randomly
      echo "<br>Found more than one exact match for sonification:<ul>";
      while ($row = $songRes->fetch_assoc()){
         echo "<li>". $row['songtitle']. "\n   <strong> " . $row['score'] . "</strong></li>\n";
      }
      echo "</ul>";
      $rInd = rand(0, $songRes->num_rows-1); 
      //echo $rInd . "<br>";
      $songRes->data_seek($rInd);
      $row = $songRes->fetch_assoc();
      echo "Selection chose your sonification: ". $row['songtitle']. "\n   <strong> " . $row['score'] . "</strong><br>\n";
   }
   else{
      $songRes = $mysqli->query("SELECT * FROM ts_songs ORDER BY ABS(score - $val) LIMIT 5");
      /*check if there are equivalent closest match values and count how many occur*/
      $rInd = 1; 
      $songRes->data_seek(0);
      $comparisonRow = $songRes->fetch_assoc();
      $songRes->data_seek(1);
      while($row = $songRes->fetch_assoc()){
         if($row['score'] == $comparisonRow['score']){
            $rInd++;
         }
      }
      if($rInd-1 > 0){
         /*if more than one equivalent 'closest match'*/
         //echo $rInd . "<br>";
         echo "Found multiple equivalent closest matches for sonification: <ul>";
         for($row_no = $rInd - 1; $row_no >= 0; $row_no--){
            $songRes->data_seek($row_no);
            $row = $songRes->fetch_assoc();
            echo "<li>". $row['songtitle']. "\n   <strong> " . $row['score'] . "</strong></li>\n";
            
         }
         echo "</ul>";
         $rInd = rand(0, $rInd-1); 
         $songRes->data_seek($rInd);
         $row = $songRes->fetch_assoc();
         echo "Selection chose your sonification: ". $row['songtitle']. "\n   <strong> " . $row['score'] . "</strong><br>\n";
      }
      else{
         /*If all 'closest matches' are unique, grab the closest, at row index 0*/
         $songRes->data_seek(0);
         $row = $songRes->fetch_assoc();
         echo "You sonify: ". $row['songtitle']. "\n   <strong> " . $row['score'] . "</strong><br>\n";
      }
   }
   /*breakup song*/
   /*...now we need to do the same thing we did in regular songs to select from equivalent closest or exact matches...*/
   
   $songRes = $mysqli->query("SELECT * FROM ts_songs WHERE isBreakup = 'TRUE' AND score = $val ORDER BY ABS(score - $val)");
   if($songRes->num_rows > 1){ 
   /*if more than one exact match for song*/
      //select one of these randomly
      echo "<br>Found more than one exact match for breakup song:<ul>";
      while ($row = $songRes->fetch_assoc()){
         echo "<li>". $row['songtitle']. "\n   <strong> " . $row['score'] . "</strong></li>\n";
      }
      echo "</ul>";
      $rInd = rand(0, $songRes->num_rows-1); 
      //echo $rInd . "<br>";
      $songRes->data_seek($rInd);
      $row = $songRes->fetch_assoc();
      echo "Selection chose breakup song: ". $row['songtitle']. "\n   <strong> " . $row['score'] . "</strong><br>\n";
   }
   else{
      $songRes = $mysqli->query("SELECT * FROM ts_songs WHERE isBreakup = 'TRUE' ORDER BY ABS(score - $val) LIMIT 5");
      /*check if there are equivalent closest match values and count how many occur*/
      $rInd = 1; 
      $songRes->data_seek(0);
      $comparisonRow = $songRes->fetch_assoc();
      $songRes->data_seek(1);
      while($row = $songRes->fetch_assoc()){
         if($row['score'] == $comparisonRow['score']){
            $rInd++;
         }
      }
      if($rInd-1 > 0){
         /*if more than one equivalent 'closest match'*/
         //echo $rInd . "<br>";
         echo "Found multiple equivalent closest breakup matches: <ul>";
         for($row_no = $rInd - 1; $row_no >= 0; $row_no--){
            $songRes->data_seek($row_no);
            $row = $songRes->fetch_assoc();
            echo "<li>". $row['songtitle']. "\n   <strong> " . $row['score'] . "</strong></li>\n";
            
         }
         echo "</ul>";
         $rInd = rand(0, $rInd-1); 
         $songRes->data_seek($rInd);
         $row = $songRes->fetch_assoc();
         echo "Selection chose breakup song: ". $row['songtitle']. "\n   <strong> " . $row['score'] . "</strong><br>\n";
      }
      else{
         /*If all 'closest matches' are unique, grab the closest, at row index 0*/
         $songRes->data_seek(0);
         $row = $songRes->fetch_assoc();
         echo "Your breakup song is: ". $row['songtitle']. "\n   <strong> " . $row['score'] . "</strong><br>\n";
      }
   }
   /*
   $songRes = $mysqli->query("SELECT * FROM ts_songs WHERE isBreakup = 'TRUE' ORDER BY ABS(score - $val) LIMIT 1");
   $row = $songRes->fetch_assoc();
   echo "Your breakup song: ". $row['songtitle']. "\n   <strong> " . $row['score'] . "</strong><br>\n";
   */
   /*album match*/
   $row = $albRes->fetch_assoc();
   echo "Your Swift Gen match: ". $row['album']. "\n   <strong> " . $row['score'] . "</strong>\n";
?>