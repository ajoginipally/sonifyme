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
   switch ($arg1){
      case "quiz":
      $quizJSON = array();
      $quizData = array();
      $quQuestObj = array();
      $quQuestions = array();
      $quiz = $mysqli->query("SELECT DISTINCT * FROM ts_questions ORDER BY RAND() LIMIT 8");
      $quiz->data_seek(0);
      $i = 0;
      /*
      $formString = "Quiz questions:<ol>";*/
      while ($row = $quiz->fetch_assoc()) {
               $quQuestObj["question"] = $row['question'];
               $quQuestObj["a"] = $row['a'];
               $quQuestObj["aVal"] = $row['aVal'];
               $quQuestObj["b"] = $row['b'];
               $quQuestObj["bVal"] = $row['bVal'];
               $quQuestObj["c"] = $row['c'];
               $quQuestObj["cVal"] = $row['cVal'];
               $quQuestObj["d"] = $row['d'];
               $quQuestObj["dVal"] = $row['dVal'];
               $quQuestions[$i] = $quQuestObj;
        /* $formString .= "<li>{$row['question']}<ul><li><input type=\"radio\" name=\"quiz{$i}\" value={$row['aVal']}>{$row['a']}</li><li><input type=\"radio\" name=\"quiz{$i}\" value={$row['bVal']}>{$row['b']}</li><li><input type=\"radio\" name=\"quiz{$i}\" value={$row['cVal']}>{$row['c'] }</li><li><input type=\"radio\" name=\"quiz{$i}\" value={$row['dVal']}>{$row['d']}</li></ul></li>";*/
         $i++;
      }
      $quizData["quizQuestions"]= $quQuestions;
      /*$formString .= "</ol>";
      echo strip_tags($formString, '<ol><ul><li><input>');
      */
      $i = 0;
      $breakup = $mysqli->query("SELECT DISTINCT * FROM ts_breakup ORDER BY RAND() LIMIT 5");
      $breakup->data_seek(0);
      $bQuestions = array();
      $bQuestObj= array();
      
      /*
      echo "Breakup quiz questions:<ol>";*/
      while ($row = $breakup->fetch_assoc()) {
               $bQuestObj["question"] = $row['question'];
               $bQuestObj["a"] = $row['a'];
               $bQuestObj["aVal"] = $row['aVal'];
               $bQuestObj["b"] = $row['b'];
               $bQuestObj["bVal"] = $row['bVal'];
               $bQuestObj["c"] = $row['c'];
               $bQuestObj["cVal"] = $row['cVal'];
               $bQuestObj["d"] = $row['d'];
               $bQuestObj["dVal"] = $row['dVal'];
               $bQuestions[$i] = $bQuestObj;
               
              /*echo '<li>' . $row['question'] . ' <ul> <li><input type="radio" name="breakup-'.$i.'" value="'.$row['aVal'].'">' . $row['a'] . '</li><li><input type="radio" name="breakup-'.$i.'" value="'.$row['bVal'].'">'. $row['b'] . '</li><li><input type="radio" name="breakup-'.$i.'" value="'.$row['cVal'].'">' . $row['c'] .'</li><li><input type="radio" name="breakup-'.$i.'" value="'.$row['dVal'].'">' .$row['d'].'</li></ul></li>';*/
        $i++;
      }
      $quizData["breakupQuestions"]= $bQuestions;
      $quizJSON["quizData"] = $quizData;//gave the JSON a single root element
      echo json_encode( $quizJSON, JSON_PRETTY_PRINT);
     /* echo "</ol>";*/
   break;
   
   case "results":
      $results = array();/*single root key for valid JSON*/
      $resultArr = array();/*includes key-value pairs for sonify, album, mood, and breakup song as well as a playlist array*/
      $playlist = array();//playlist songs
      
      
      
      $val = rand(1, 25);
      $resultArr['mood']=$val;
      //need to translate this numeric value into a valid mood
      
      $i = 0;
      /* found closest result solution here 6/9/2015:
      http://stackoverflow.com/questions/592209/find-closest-numeric-value-in-database
      */
      $playRes = $mysqli->query("SELECT * FROM ts_songs ORDER BY ABS(score - $val) LIMIT 5");
     // echo "Your random number: ".$val . "<br>Your Playlist:<ul>";
      /*spit out closest five songs to value from random number*/
      while ($row = $playRes->fetch_assoc()) {
         $playlist[$i] = $row['songtitle'];
        // echo "<li>" . $row['songtitle'] . "\n   <strong> " . $row['score'] . "</strong>\n</li>";
        $i++;
      }
      $resultArr['playlist'] = $playlist;
      //echo "</ul>";
      $i=0;
      
      $albRes = $mysqli->query("SELECT * FROM ts_albums ORDER BY ABS(score - $val) LIMIT 1");
     
      /*for both sonify song and breakup song: cover cases where a) there 
      are more than one exact match b) there are more than one equivalent closest 
      match in addition to c) there is a single closest match.
      There is probably a more efficient and less-verbose way of doing this, but 
      what's here seems to work*/
      $songRes = $mysqli->query("SELECT * FROM ts_songs WHERE score = $val");
      if($songRes->num_rows > 1){ 
      /*if more than one exact match for song
         //select one of these randomly
        // echo "<br>Found more than one exact match for sonification:<ul>";
        // while ($row = $songRes->fetch_assoc()){
        //    echo "<li>". $row['songtitle']. "\n   <strong> " . $row['score'] . "</strong></li>\n";
        // }
        // echo "</ul>";*/
         $rInd = rand(0, $songRes->num_rows-1); 
         //echo $rInd . "<br>";
         $songRes->data_seek($rInd);
         $row = $songRes->fetch_assoc();
        // echo "Selection chose your sonification: ". $row['songtitle']. "\n   <strong> " . $row['score'] . "</strong><br>\n";
         $resultArr["sonify"] = $row['songtitle'];
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
            /*if more than one equivalent 'closest match'
            //echo $rInd . "<br>";
            //echo "Found multiple equivalent closest matches for sonification: <ul>";*/
            for($row_no = $rInd - 1; $row_no >= 0; $row_no--){
               $songRes->data_seek($row_no);
               $row = $songRes->fetch_assoc();
              // echo "<li>". $row['songtitle']. "\n   <strong> " . $row['score'] . "</strong></li>\n";
               
            }
            //echo "</ul>";
            $rInd = rand(0, $rInd-1); 
            $songRes->data_seek($rInd);
            $row = $songRes->fetch_assoc();
           /* echo "Selection chose your sonification: ". $row['songtitle']. "\n   <strong> " . $row['score'] . "</strong><br>\n";*/
            $resultArr["sonify"] = $row['songtitle'];
         }
         else{
            /*If all 'closest matches' are unique, grab the closest, at row index 0*/
            $songRes->data_seek(0);
            $row = $songRes->fetch_assoc();
           // echo "You sonify: ". $row['songtitle']. "\n   <strong> " . $row['score'] . "</strong><br>\n";
            $resultArr["sonify"] = $row['songtitle'];
         }
      }
      /*breakup song*/
      /*...now we need to do the same thing we did in regular songs to select from equivalent closest or exact matches...*/
      
      $songRes = $mysqli->query("SELECT * FROM ts_songs WHERE isBreakup = 'TRUE' AND score = $val ORDER BY ABS(score - $val)");
      if($songRes->num_rows > 1){ 
      /*if more than one exact match for song
         //select one of these randomly
        // echo "<br>Found more than one exact match for breakup song:<ul>";
         //while ($row = $songRes->fetch_assoc()){
         //   echo "<li>". $row['songtitle']. "\n   <strong> " . $row['score'] . "</strong></li>\n";
        // }
        // echo "</ul>";*/
         $rInd = rand(0, $songRes->num_rows-1); 
         //echo $rInd . "<br>";
         $songRes->data_seek($rInd);
         $row = $songRes->fetch_assoc();
        // echo "Selection chose breakup song: ". $row['songtitle']. "\n   <strong> " . $row['score'] . "</strong><br>\n";
         $resultArr["sonify"] = $row['songtitle'];
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
            /*if more than one equivalent 'closest match'
            //echo $rInd . "<br>";
           // echo "Found multiple equivalent closest breakup matches: <ul>";
           // for($row_no = $rInd - 1; $row_no >= 0; $row_no--){
           //    $songRes->data_seek($row_no);
           //    $row = $songRes->fetch_assoc();
              // echo "<li>". $row['songtitle']. "\n   <strong> " . $row['score'] . "</strong></li>\n";
               
           // }
            //echo "</ul>";*/
            $rInd = rand(0, $rInd-1); 
            $songRes->data_seek($rInd);
            $row = $songRes->fetch_assoc();
            //echo "Selection chose breakup song: ". $row['songtitle']. "\n   <strong> " . $row['score'] . "</strong><br>\n";
             $resultArr["breakup"] = $row['songtitle'];
         }
         else{
            /*If all 'closest matches' are unique, grab the closest, at row index 0*/
            $songRes->data_seek(0);
            $row = $songRes->fetch_assoc();
           // echo "Your breakup song is: ". $row['songtitle']. "\n   <strong> " . $row['score'] . "</strong><br>\n";
            $resultArr["breakup"] = $row['songtitle'];
         }
      }
      /*
      $songRes = $mysqli->query("SELECT * FROM ts_songs WHERE isBreakup = 'TRUE' ORDER BY ABS(score - $val) LIMIT 1");
      $row = $songRes->fetch_assoc();
      $resultArr["breakup"] = $row['songtitle'];
      echo "Your breakup song: ". $row['songtitle']. "\n   <strong> " . $row['score'] . "</strong><br>\n";
      */
      /*album match*/
      $row = $albRes->fetch_assoc();
      $resultArr['album'] = $row['album'];
     /* echo "Your Swift Gen match: ". $row['album']. "\n   <strong> " . $row['score'] . "</strong>\n";*/
     $results['results']= $resultArr;
      echo "\n".json_encode( $results, JSON_PRETTY_PRINT);
      break;
      default:
         trigger_error("Something went wrong...");
         die;
   }
   ?>