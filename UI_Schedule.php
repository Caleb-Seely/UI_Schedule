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

// Database Connection

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "information_schema";
$search_result = '';
   // create connection
   $conn =  mysqli_connect($servername, $username, $password, $dbname);
   // Check connection
   if($conn->connect_error){
      die($conn->connect_error);
   }

// Button Actions

if(isset($_POST['submit'])){
 
   $current_ID = $_POST["student_ID"];
   $search_result = '';

   $inputQuery = trim($_POST['inputQuery']);

   $full_input = explode('.', $inputQuery);     //Change ; -> . for the public
   $command_count = 0;

   do{
      $inputQuery = $full_input[$command_count];
      
      if(strpos(strtolower('###'.$inputQuery), 'create database')){
         //updateMessages('error', 'Database creation not allowed on this platform.');
      }
      else if(strpos(strtolower('###'.$inputQuery), 'drop database')){
            //updateMessages('error', 'Database deletion not allowed on this platform.');

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
                  //updateMessages('error', $conn->error);
               }

               $success = mysqli_next_result($conn); echo $success;
               if(!$success){
                  //updateMessages('error', 'Database operation error');
               }
               else{
                  $search_result = mysqli_store_result($conn);
               }
            }
            while($success);
         }

            //$search_result = mysqli_store_result($conn);

            $search_result = $conn->query($inputQuery);     //Work horse 

            if(is_bool($search_result) and $search_result){
               $operation = substr($inputQuery, 0, strpos($inputQuery, ' '));
               //updateMessages('success', ucfirst($operation).' operation successfully executed.');
            }  
      }
      $command_count = $command_count+1;
      
   } while(isset($full_input[$command_count]) );

   //retrieve colmn names to display in output table

   $col_names = '';
   if(strpos(strtolower('###'.substr(trim($inputQuery),0,7)), 'select')){
      preg_match('/(?<=select )(.*)(?= from)/', $inputQuery, $regexResults);
      //$col_names = $regexResults[0];
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
         if($msgStatus == 'success'){array_push($successMsg, $msgIndex.' '.$msg);}
         else{ array_push($errorMsg, $msgIndex.' '.$msg);}
      }
}

function check_command(){
   GLOBAL $inputQuery;
   GLOBAL $S_ID;
   GLOBAL $current_ID;
   Global $full_input;
   $command = array('');
   $command = explode(' ', $inputQuery);
   $name = "Blank";
   
   $year = 1;
   $Grad_Year = "Blank";
   $Goal = "Blank";
   $class_ID = "Blank";
   $Level = "Blank";
   $Industry = "ALL";

   if(strpos(strtolower('###'.$command[0]), 'add')){     //Add a user to the system
      if(isset($command[1])){ $name = $command[1];}      //student name
      if(isset($command[2])){ $S_ID = $command[2]; $current_ID = $S_ID;  }
      if(isset($command[3])){ $Goal = $command[3];}
      
      $full_input[1] = "taken tmp";
      $inputQuery = "Insert into cs_classes.users (Name, S_ID, Goal)\nValues('$name','$S_ID', '$Goal'); ";
      updateMessages('success' ,$name .' with the ID '. $S_ID.' and the target industry '. $Goal .' has been added.' );
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
         if(!strpos(strtolower('###'.$class_ID), 'tmp')){
            updateMessages('success', 'ID: '. $S_ID. ' has taken '. $class_ID);
         }
         
   }
   else if(strpos(strtolower('###'.$command[0]), 'class')){    //Setup function to fill the class Db
      if(isset($command[1])){ $name = $command[1];}      //Class name
      if(isset($command[2])){ $class_ID = $command[2];}
      if(isset($command[3])){ $Industry = $command[3];}
      if(isset($command[4])){ $Level = $command[4];}

      $inputQuery = "Insert into cs_classes.class_list (class_ID, class_Name, Industry, Level)\nValues('$class_ID', '$name', '$Industry', '$Level')";
   }
   else if(strpos(strtolower('###'.$command[0]), 'find')){
      if(strpos(strtolower('###'.$command[1]), 'freshman')){$year = 1;}
      if(strpos(strtolower('###'.$command[1]), 'sophomore')){$year = 2;}
      if(strpos(strtolower('###'.$command[1]), 'junior')){$year = 3;}
      if(strpos(strtolower('###'.$command[1]), 'senior')){$year = 4;}

      $inputQuery = "Select Distinct class_Name FROM cs_classes.class_list, cs_classes.users, cs_classes.classes_taken where (users.Goal = class_list.Industry or class_list.Industry = 'All') and ($year = class_list.level) and (classes_taken.S_ID = $current_ID and class_list.class_ID != classes_taken.class_ID)  and users.S_ID = $current_ID";
   }
   else if(strpos(strtolower('###'.$command[0]), 'update')){
      if(strpos(strtolower('###'.$command[1]), 'year')){
         if(isset($command[2])){$Grad_Year = $command[2];}
         $inputQuery="Update cs_classes.users\nSet Grad_Year = '$Grad_Year'\nWhere S_ID = '$current_ID'";
      }
      else if(strpos(strtolower('###'.$command[1]), 'industry')){
         if(isset($command[2])){$Goal = $command[2];}
         $inputQuery = "update cs_classes.users\nSet Goal = '$Goal'\nWhere S_ID = '$current_ID'";

      }
   }
   else if(strpos(strtolower('###'.$command[0]), 'completed')){
      $inputQuery = "Select class_Name from cs_classes.class_list, cs_classes.classes_taken where classes_taken.S_ID = $current_ID and class_list.class_ID =classes_taken.class_ID";
   }
   else {

      echo $inputQuery;
      $inputQuery = "SELECT class_Name FROM cs_classes.class_list ";
      updateMessages('success', 'Nothing requested. Here is a full class list.');     

   }
   
   //echo $inputQuery;
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

         <h2><b>UI Schedule Planner</b></h2>



         <form action = "UI_Schedule.php" method = "post" id = "options">

         <!---QUERY OPTIONS SECTION--->

            <section class = "block-of-text">
               <fieldset>
                  <legend><b>Student ID</b></legend>

                  <input type = "text" id="student_ID" name="student_ID" placeholder = "Student ID here" value =  
                  <?php 
                  if($current_ID != 0)
                        echo $current_ID;
                      
                  ?> > </input>

                  <br>

               </fieldset>
            </section>

            <!--INPUT SECTION-->

            <section class = "block-of-text">
                  <fieldset>
                     <legend><b>Command</b></legend>

                        <textarea class = "FormElement" name = "inputQuery" id = "input" cols = "80"
 rows = "10" placeholder = "Enter command here."></textarea>

                        <br>

                        <input type = "submit" id ="submit" name= "submit" value ="Submit" onclick = "return checkInput();">

                  </fieldset>
               </section>
            </form>

            <!--OUTPUT SECTION-->
            <form action = "UI_Schedule.php" method = "post">

                  <section class = "block-of-text">
                     <fieldset>
                        <legend><b>Output</b></legend>

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
                     <fieldset>
                           <legend><b>Instructions</b></legend>
                           Words in all caps are user dependent.</br>
                           -To add a user: Add NAME STUDENT_ID INDUSTRY</br><b>Industry options are:</b> Security, Games, Software, Hardware or Data.<br>
                           -To request your class list: Find Freshman | Find Sophomore | Find Junior | Find Senior </br>
                           -To change industry: Update industry INDUSTRY.<br>
                           -To add classes already taken: Taken CS_120 | CS___ depending on the class.</br>   
                           -For a full list of classes already completed: Completed.</br>
                           A blank submission will result in a full CS class list as of Spring 2020.                    
                        </fieldset>
                     </section>

                     <section class = "block-of-text">
                        <a href="UI_Schedule.php"><input type = "submit" name = "reset" value = "Reset Page"/></a>
                     </section>

                     <?php $conn->close(); ?>

                     <script src = "effects.js"></script>

                     
                     
                        

                  </body>
               </html>