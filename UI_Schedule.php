<?php
// Global Perameters

$msgIndex = 0;

$targetDB = '';
$querytype = 'sql';
$inputQuery = '';
$full_input = array('');
$userInput = 'Input text here';
$S_ID = 0;
$current_ID = ($S_ID);

$tableName = '';
$selection = '';

$errorMsg = array('');
$successMsg = array('');
$defaultTables = ['information_schema', 'mysql', 'performace_schema', 'sakila', 'sys', 'world'];

// Database Connection

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "information_schema";

   // create connection
   $conn =  mysqli_connect($servername, $username, $password, $dbname);
   // Check connection
   if($conn->connect_error){
      die($conn->connect_error);
   }

// Button Actions

if(isset($_POST['submit'])){
 
   $current_ID = $_POST["student_ID"];
   $search_result = NULL;

   $inputQuery = trim($_POST['inputQuery']);

   $full_input = explode(';', $inputQuery);     //Change ; -> . for the public
   $command_count = 0;

   do{
      $inputQuery = $full_input[$command_count];
      
      if(strpos(strtolower('###'.$inputQuery), 'create database')){
         updateMessages('error', 'Database creation not allowed on this platform.');
      }
      else if(strpos(strtolower('###'.$inputQuery), 'drop database')){
            updateMessages('error', 'Database deletion not allowed on this platform.');

      }
      else
      {  check_command();
         if(0)//(mysql_multi_query($conn, str_replace('<br>', '',$inputQuery)))
         {
            do{
               //check first result
               if($result = mysqli_store_result()){
                  $search_result = $result; echo $inputQuery;
                  //free the result and move on to next query
                  mysqli_free_result($result); 
               }
               else{
                  updateMessages('error', $conn->error);
               }

               $success = mysqli_next_result($conn); echo $success;
               if(!$success){
                  updateMessages('error', $conn->error);
               }
               else{
                  $search_result = mysqli_store_result($conn);
               }
            }
            while($success);
         }

            //$search_result = mysqli_store_result($conn);
            $search_result = $conn->query($inputQuery);
            if(is_bool($search_result) and $search_result){
               $operation = substr($inputQuery, 0, strpos($inputQuery, ' '));
               updateMessages('success', ucfirst($operation).' operation successfully executed.');
            }  
      }
      $command_count += 1;
   } while(isset($full_input[$command_count]) );

   //retrieve colmn names to display in output table

   $col_names = '';
   if(strpos(strtolower('###'.substr(trim($inputQuery), 0, 7)), 'select')){
      preg_match('/(?<=select )(.*)(?= from)/', $inputQuery, $regexResults);
      $col_names = $regexResults[0];
   }
   if(strpos(strtolower('###'.substr(trim($inputQuery), 0, 7)), 'show')){
      $col_names = 'show';
   }

   if($col_names == '*' or strtolower($col_names) == 'show'){

      if(strtolower($col_names) == 'show'){ $q = rtrim($inputQuery, ';');}
      else{
         $q = $inputQuery;
         if(strpos($q, 'limit'))    #remove any occurence of limit
         {
            $q = substr($q, 0, strpos($q, 'limit'));
         }

         $q = rtrim($q, ';').' limit 1';
      }

      $col_names = '';

      if($result = mysqli_query($conn, $q)){
         // Get field information for all fields
         while($fieldinfo = mysqli_fetch_field($result)){
            $col_names .= $fieldinfo->name.'';
         }
         // Free result set
         mysqli_free_result($result);
      }
      else{
         updateMessages('error', $conn->error);
      }  
   }

   $columns = explode(" ", trim($col_names));
}

function updateMessages( $msgStatus, $msg){

      GLOBAL $msgIndex;
      GLOBAL $successMsg;
      GLOBAL $errorMsg;

      if($msg != ''){

         $msgIndex += 1;
         if($msgStatus == 'success'){array_push($successMsg, $msgIndex.'. '.$msg);}
         else{ array_push($errorMsg, $msgIndex.'. '.$msg);}
      }
}

function check_command(){
   GLOBAL $inputQuery;
   GLOBAL $S_ID;
   GLOBAL $current_ID;
   $command = array('');
   $command = explode(' ', $inputQuery);
   $name = "Blank";
   
   $Grad_Year = "Blank";
   $Goal_Job = "Blank";
   $class_ID = "Blank";
   $Level = "Blank";
   $Industry = "ALL";

   if(strpos(strtolower('###'.$command[0]), 'add')){     //Add a user to the system
      if(isset($command[1])){ $name = $command[1];}      //student name
      if(isset($command[2])){ $S_ID = $command[2]; $current_ID = $S_ID;  }
      if(isset($command[3])){ $Grad_Year = $command[3];}
      if(isset($command[4])){ $Goal_Job = $command[4];}
      $inputQuery = "Insert into cs_classes.users (Name, S_ID, Grad_Year, Goal_Job)\nValues('$name','$S_ID', '$Grad_Year', '$Goal_Job');";
      updateMessages('success' ,$name .' ID: '. $S_ID. ' Graduation: '. $Grad_Year .' Industry: '. $Goal_Job .' added.' );
   }
   else if(strpos(strtolower('###'.$command[0]), 'taken')){
         if(isset($command[1])){ $class_ID = $command[1];}      //Class name
         //if(isset($command[2])){ $S_ID = $command[2];}
         if($current_ID != 0){
            $S_ID = $current_ID;
         }
         else{
            updateMessages('fail', 'Please enter a valid student ID');
         }
         $inputQuery = "Insert into cs_classes.classes_taken (S_ID, Class_ID)\nValues('$S_ID','$class_ID')";
         updateMessages('success', 'ID: '. $S_ID. ' has taken '. $class_ID);
   }
   else if(strpos(strtolower('###'.$command[0]), 'class')){    //Setup function to fill the class Db
      if(isset($command[1])){ $name = $command[1];}      //Class name
      if(isset($command[2])){ $class_ID = $command[2];}
      if(isset($command[3])){ $Industry = $command[3];}
      if(isset($command[4])){ $Level = $command[4];}

      $inputQuery = "Insert into cs_classes.class_list (class_ID, class_Name, Industry, Level)\nValues('$class_ID', '$name', '$Industry', '$Level')";
   }
   else if(strpos(strtolower('###'.$command[0]), 'find')){
      if(isset($command[1])){$year = $command[1];}

      $inputQuery = "Select class_Name FROM cs_classes.class_list, cs_classes.users where (users.Goal_Job = class_list.Industry or class_list.Industry = 'All') and users.S_ID = $current_ID";
      echo $inputQuery;
   }

}
?>

<!-------------HTML------------------->

<!DOCTYPE html>
<html>

   <head>
      <title>UI Schedule Planner</title>
      <link href = 'style.css' rel = 'stylesheet'>
      </head>

      <body>

         <h2>UI Schedule</h2>

         <section class="block-of-text" style="display: none;">
            <button class="collapsible">See Example Usage</button>
            <div class="content">
               <fieldset class = "side">
                  <legend>Sample Database</legend>

               </fieldset>

               <fieldset class = "side">
                  <legend>Sample Updates</legend>

               </fieldset>

               <fieldset class = "side">
                  <legend>Sample UpdatQueries</legend>

               </fieldset>
            </div>
         </section>

         <form action = "UI_Schedule.php" method = "post" id = "options">

         <!---QUERY OPTIONS SECTION--->

            <section class = "block-of-text">
               <fieldset>
                  <legend>Student ID</legend>

                  <input type = "text" id="student_ID" name="student_ID" value =  
                  <?php 
                        echo $current_ID;
                      
                  ?> > </input>

                  <br>

               </fieldset>
            </section>

            <!--INPUT SECTION-->

            <section class = "block-of-text">
                  <fieldset>
                     <legend>Input</legend>

                        <textarea class = "FormElement" name = "inputQuery" id = "input" cols = "40"
 rows = "10" placeholder = <?php echo $inputQuery; ?>></textarea>

                        <br>

                        <input type = "submit" id ="submit" name= "submit" value ="Submit" onclick = "return checkInput();">

                  </fieldset>
               </section>
            </form>

            <!--OUTPUT SECTION-->
            <form action = "UI_Schedule.php" method = "post">

                  <section class = "block-of-text">
                     <fieldset>
                        <legend>Output</legend>

                           <?php $messages = array_merge($successMsg, $errorMsg); asort($messages); ?>
                              <?php foreach($messages as $msg):?>
                                 <b><?php if($msg !== ''){echo $msg.'<br>';} ?></b>
                              <?php endforeach; ?>

                              <br>

                              <?php if($search_result and !is_bool($search_result)): ?>

                                 <table>
                                    

                                    <!--populate table-->
                                    <?php if($search_result and $search_result != ''):?>
                                       <?php while($row = mysqli_fetch_array($search_result)):?>
                                          <tr>
                                             <?php foreach($columns as $col):?>
                                                <td><?php echo $row[0];?></td>
                                             <?php endforeach; ?>
                                          </tr>
                                       <?php endwhile;?>
                                    <?php endif?>
                                 </table>

                              <?php endif?>

                           </fieldset>
                        </section>
                     </form>

                     <section class = "block-of-text">
                        <a href="UI_Schedule.php"><input type = "submit" name = "reset" value = "Reset Page"/></a>
                     </section>

                     <?php $conn->close(); ?>

                     <script src = "effects.js"></script>

                  </body>
               </html>