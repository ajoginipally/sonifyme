<?php
   /*
   determine which section of php should be run, using:
   db.php?which=quiz  or db.php?which=breakup  or db.php?which=results
   */
   $arg1 = $_GET['which'];
   
   /*super-basic boilerplate connection code*/
   $mysqli = new mysqli("localhost", "root", "", "sonifydb");
   if ($mysqli->connect_errno) {
       echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
   }
   
   /*** 
   $which: determines which section of dp.php to run
   $res: all songs
   $bResAll: all breakup songs
   $playRes: The 5 rows closest to a given score value 
   $albRes: The closest match album row to a given score value
   $val: a random number from 1 to 25 inclusive
   $rInd: random number from 0 to $songRes->num_rows-1 to select from multiple
      matches
   $songRes: currently used both for single song selection and breakup song 
      selection
   $quiz: 8 randomly-selected rows for quiz questions
   $breakup: 5 randomly-selected rows for breakup questions
   ***/
   if($arg1 == 'quiz'){

      $quiz = $mysqli->query("SELECT DISTINCT * FROM ts_questions ORDER BY RAND() LIMIT 8");
      $quiz->data_seek(0);
      $i = 0;
      echo "Quiz questions;<ol>";
      while ($row = $quiz->fetch_assoc()) {
         echo '<li>' . $row['question'] . '  <ul> <li><input type="radio" name="quiz-'.$i.'" value="'.$row['aVal'].'">' . $row['a'] . '</li><li><input type="radio" name="quiz-'.$i.'" value="'.$row['bVal'].'">'. $row['b'] . '</li><li><input type="radio" name="quiz-'.$i.'" value="'.$row['cVal'].'">' . $row['c'] .'</li><li><input type="radio" name="quiz-'.$i.'" value="'.$row['dVal'].'">' .$row['d'].'</li></ul></li>';
         $i++;
      }
      echo "</ol>";
      }
   else if($arg1 == 'breakup'){
      $i = 0;
      $breakup = $mysqli->query("SELECT DISTINCT * FROM ts_breakup ORDER BY RAND() LIMIT 5");
      $breakup->data_seek(0);
      echo "Breakup quiz questions:<ol>";
      while ($row = $breakup->fetch_assoc()) {
              echo '<li>' . $row['question'] . ' <ul> <li><input type="radio" name="breakup-'.$i.'" value="'.$row['aVal'].'">' . $row['a'] . '</li><li><input type="radio" name="breakup-'.$i.'" value="'.$row['bVal'].'">'. $row['b'] . '</li><li><input type="radio" name="breakup-'.$i.'" value="'.$row['cVal'].'">' . $row['c'] .'</li><li><input type="radio" name="breakup-'.$i.'" value="'.$row['dVal'].'">' .$row['d'].'</li></ul></li>';
        $i++;
      }
      echo "</ol>";
   }
   else if($arg1 == 'results'){
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
   }
   else {
      trigger_error("Something went wrong...");
      die;
   }
   ?>