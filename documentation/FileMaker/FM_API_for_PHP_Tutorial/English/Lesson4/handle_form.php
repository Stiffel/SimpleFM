<?php
    /**
    * FileMaker PHP Example
    *
    *
    * Copyright 2006, FileMaker, Inc.  All rights reserved.
    * NOTE: Use of this source code is subject to the terms of the FileMaker
    * Software License which accompanies the code. Your use of this source code
    * signifies your agreement to such license terms and conditions. Except as
    * expressly granted in the Software License, no other copyright, patent, or
    * other intellectual property license or right is granted, either expressly or
    * by implication, by FileMaker.
    *
    */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">

<html>
<head>
    <title>Questions</title>
</head>
<body>

    <?php include("dbaccess.php"); ?>

    <h1>FileMaker Questionnaire System</h1>
    <hr />
    <?php
        //'respondent_exists' will be set if the user is coming from the Respondent.php page
    
        if (isset($_POST['respondent_exists']))  {
            
            //Grab the user input from the $_POST data    
            $respondent_data = array(
                                        'Prefix'            => $_POST['prefix'],
                                        'First Name'        => $_POST['first_name'],
                                        'Last Name'         => $_POST['last_name'],
                                        'questionnaire_id'  => $_POST['active_questionnaire_id'],
                                        'Email Address'     => $_POST['email']
                                    );
    
            //Validate the user input.
            if (    empty($respondent_data['Prefix']) 
                ||  empty($respondent_data['First Name'])
                ||  empty($respondent_data['Last Name'])
                ||  empty($respondent_data['Email Address'])) {
                
                //If data is missing, prompt them with a message.
                echo '<h3>Some of your information is missing. Please go back and fill out all of the fields.</h3>';
                exit;
    
            } else {
                
                //If user input is valid, add the first name, last name and the email address in the Respondent layout
                $newRequest =& $fm->newAddCommand('Respondent', $respondent_data);
                $result = $newRequest->execute();
                
                //check for an error
                if (FileMaker::isError($result)) {
                    echo "<p>Error: " . $result->getMessage() . "<p>";
                    exit;
                }
                
                $records = $result->getRecords();
                $record = $records[0];
                $respondent_recid = $record->getField('Respondent ID');
            }
    
            
            /*
             * Now, we need to get the first question in the questionnaire and display it to the user.
             */
    
            //Get the active questionnaire and it's record id.
            $active_questionnaire_id = $_POST['active_questionnaire_id'];
            $active_questionnaire = $fm->getRecordById('questionnaire',$active_questionnaire_id);    

            //Perform a find on the 'Questions' layout for questions that belong to this questionnaire    
            $findCommand =& $fm->newFindCommand('Questions');    
            $findCommand->addFindCriterion('Questionnaire ID', $active_questionnaire_id);    
            $result = $findCommand->execute();
            
            //check for an error
            if (FileMaker::isError($result)) {
                echo "<p>Error: " . $result->getMessage() . "<p>";
                exit;
            }
    
    
    
            //Get the question and Question Type of the first Question Record.    
            $records = $result->getRecords();
            $question = $records[0];    
            $real_question = $question->getField('question');    
            $question_type = $question->getField('Question Type');    
            $cur_question = $records[0]->getRecordID();
    
            //Print the question out.    
            echo "<p>".$real_question."</p>";    
            echo '<form action="thankyou.php" method= "POST">';            
    
            /*
             * Output the appropriate HTML form element based on the value of $question_type
             */
    
            if ($question_type == "text" ) {
    
                //display a text input    
                echo '<input type="text" name="text_answer" size="60" value=""/>';
                
            } else if ($question_type =="radio" || $question_type =="ranking") {
                
                /*
                 * If the question_type calls for radio buttons, we need to retrieve a
                 * list of acceptable responses to this question.
                 * Note: Questions of type 'radio' and 'ranking' are implemented identically
                 * and both use radio buttons for user input.
                 */
    
                //Get the portal 'question_answers'
    
                $relatedSet = $question->getRelatedSet('question_answers');
    
                //check for an error
                if (FileMaker::isError($relatedSet)) {
                    echo "<p>Error: " . $relatedSet->getMessage(). "</p>";
                    exit;
                }
                
                //display each of the possible answers as a HTML radio button
                foreach ($relatedSet as $relatedRow) {
                    $possible_answer = $relatedRow->getField('question_answers::answer');
                    echo '<input type= "radio" name= "radio_answer" value= "'. $possible_answer .'">' . $possible_answer . '<br/>'; 
                }
    
            } else if ($question_type == "pulldown") {
    
                /*
                 * If the question_type calls for a pulldown menu, we need to retrieve a
                 * list of acceptable responses to this question.
                 */
                
                //Get the portal 'question_answers'
                $relatedSet = $question->getRelatedSet('question_answers');
                
                //check for an error
                if (FileMaker::isError($relatedSet)) {
                    echo "<p>Error: " . $relatedSet->getMessage(). "</p>";
                    exit;
                }
                
                //print the start tag for a HTML pulldown menu
                echo '<select name="pulldown">';
                
                //display each of the possible answers as an option in the HTML pulldown menu
                foreach ($relatedSet as $relatedRow)  {
                    $possible_answer = $relatedRow->getField('question_answers::answer');
                    echo '<option value="' . $possible_answer .'">' . $possible_answer . '</option>'; 
                 }
                
                //print the end tag for a HTML pulldown menu
    
                echo '</select>';
    
            } else if($question_type == "checkbox") {
               
                /*
                 * If the question_type calls for checkboxes, we need to retrieve a
                 * list of acceptable responses to this question.
                 */
                
                //Get the portal 'question_answers'
                $relatedSet = $question->getRelatedSet('question_answers');
                
                //check for an error
                
                if (FileMaker::isError($relatedSet)) {
                    echo "<p>Error: " . $relatedSet->getMessage(). "</p>";
                    exit;
                }
                
                //display each of the possible answers as a HTML checkbox
                foreach ($relatedSet as $relatedRow) {
                    $possible_answer = $relatedRow->getField('question_answers::answer');
                    echo '<input type= "checkbox" name="cbanswer[]" value= "' . $possible_answer . '"/ >' . $possible_answer . '<br/>';
                }
    
            } else {
                //If $question_type is undefined or unrecognized, default to a HTML text input
                echo '<input type="text" name="text_answer" size="60" value=""/>';
            }
            
            echo '<hr />';
            
            /*
             * Here, we set the hidden form values that are passed to the next page via $_POST.
             * 
             *         'store_information' -- always set, tells the next page to save the response to THIS question
             *         'question_type' -- the format of the response (text, radio, ranking, pulldown, or checkbox)
             *         'respondent_id' -- the record id of the Respondent record
             *         'cur_question' -- the record id of the current Question record
             */
           
           echo '<input type="hidden" name="store_information" value="store_information"/>';
            echo '<input type="hidden" name="question_type" value="' . $question_type . '">';              
            echo '<input type="hidden" name="respondent_id" value="' . $respondent_recid . '"/>';
            echo '<input type="hidden" name="cur_question" value="' . $cur_question . '"/>';
            echo '<input type="Submit" name="submit" value="Submit" />';
            
            //print the end tag for the HTML form
            echo '</form>';
        }
    ?>

</body>

</html>

