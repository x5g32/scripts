<?php
// **************************************************************
// Test Password Strength Function
// **************************************************************
// Result is returned as an array as follows:
// $result[1] = the submitted password
// $result[2] = the returned strength score out of 100
// $result[3] = strength assessment (good, poor, etc)
// $result[4] = an array of warning messages
// $result[5] = an array of each char in password not repeated
// $result[6] = an array of the number of times each char in
//              the char password array occurs in the password
// **************************************************************
function pass_strength($password) {

  // define error message array
  $msg = array();

  if($password == '') {
    $msg[] = 'You did not submit a password';
  }

  $password = trim($password);
  $password = preg_replace('/\s/', '', $password);

  $nums  = '0';
  $lower = '0';
  $upper = '0';
  $spcl  = '0';

  // *********************************************
  // num of chars in the password
  // *********************************************
  $length = strlen($password);


  // *********************************************
  // Scoring
  // *********************************************
  $score = number_format('0', '1');


  // *********************************************
  // check if numbers exist
  // *********************************************
  for($i=0; $length>$i; $i++) {
    // extract char
    if(function_exists('mb_substr')) {
      $char = mb_substr($password, $i, 1);
      } else {
      $char = substr($password, $i, 1);
    } // if

    if(is_numeric($char) == true) {
      $nums = $nums + 1;
    }
  } // for


  // *********************************************
  // check if lower case letters exist
  // *********************************************
  $lowerref = 'abcdefghijklmnopqrstuvwxyz';

  for($i=0; $length>$i; $i++) {
    // extract char
    if(function_exists('mb_substr')) {
      $char = mb_substr($password, $i, 1);
      } else {
      $char = substr($password, $i, 1);
    } // if

    if(strpos($lowerref, $char) !== false) {
      $lower = $lower + 1;
    }
  } // for


  // *********************************************
  // check if upper case letters exist
  // *********************************************
  $upperref = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

  for($i=0; $length>$i; $i++) {
    // extract char
    if(function_exists('mb_substr')) {
      $char = mb_substr($password, $i, 1);
      } else {
      $char = substr($password, $i, 1);
    } // if

    if(strpos($upperref, $char) !== false) {
      $upper = $upper + 1;
    }
  } // for


  // *********************************************
  // check if special chars exist
  // *********************************************
  $spclref  = '~`!@#$%^&*()_+-=|{}[]:;"/<>.,?\\';
  $spclref .= "'";

  for($i=0; $length>$i; $i++) {
    // extract char
    if(function_exists('mb_substr')) {
      $char = mb_substr($password, $i, 1);
      } else {
      $char = substr($password, $i, 1);
    } // if

    if(strpos($spclref, $char) !== false) {
      $spcl = $spcl + 1;
    }
  } // for


  // *********************************************
  // account for unrecognized chars
  // *********************************************
  $tot = $nums + $lower + $upper + $spcl;

  if($length > $tot) {
    $unrec = $length - $tot;
  }
  else {
    $unrec = '0';
  }


  // *********************************************
  // numbers scoring
  // *********************************************
  if($nums !== '0') {
    $score = $score + 1;
    $score = number_format($score, '1');
  }
  else {
    $score = $score - 0.2;
    $score = number_format($score, '1');
    $msg[] = 'Password does not contain any numbers';
  }

  if(2 > $nums && $nums !== '0') {
    $score = $score - 0.1;
    $score = number_format($score, '1');
    $msg[] = 'Password only has one number';
  }

  if($nums >= 2) {
    $score = $score + 0.2;
    $score = number_format($score, '1');
  }


  // *********************************************
  // lowercase scoring
  // *********************************************
  if($lower !== '0') {
    $score = $score + 1;
    $score = number_format($score, '1');
  }
  else {
    $score = $score - 0.2;
    $score = number_format($score, '1');
    $msg[] = 'Password does not contain any lowercase letters';
  }

  if(2 > $lower && $lower !== '0') {
    $score = $score - 0.1;
    $score = number_format($score, '1');
    $msg[] = 'Password only has one lowercase letter';
  }

  if($lower >= 3) {
    $score = $score + 0.2;
    $score = number_format($score, '1');
  }


  // *********************************************
  // uppercase scoring
  // *********************************************
  if($upper !== '0') {
    $score = $score + 1;
    $score = number_format($score, '1');
  }
  else {
    $score = $score - 0.2;
    $score = number_format($score, '1');
    $msg[] = 'Password does not contain any uppercase letters';
  }

  if(2 > $upper && $upper !== '0') {
    $score = $score - 0.1;
    $score = number_format($score, '1');
    $msg[] = 'Password only has one uppercase letter';
  }

  if($upper >= 3) {
    $score = $score + 0.2;
    $score = number_format($score, '1');
  }


  // *********************************************
  // spcl scoring
  // *********************************************
  if($spcl !== '0') {
    $score = $score + 1;
    $score = number_format($score, '1');
  }
  else {
    $score = $score - 0.1;
    $score = number_format($score, '1');
    $msg[] = 'Password does not contain any special characters';
  }

  if(2 > $spcl && $spcl !== '0') {
    $score = $score - 0.1;
    $score = number_format($score, '1');
    $msg[] = 'Password only has one special character';
  }

  if($spcl >= 2) {
    $score = $score + 0.2;
    $score = number_format($score, '1');
  }


  // *********************************************
  // unrecognized char scoring
  // *********************************************
  if($unrec !== '0') {
    $score = $score + 0.5;
    $score = number_format($score, '1');

    if($unrec >= 2) {
      $score = $score + 0.2;
      $score = number_format($score, '1');
    }
  }


  // *********************************************
  // if password is all one char scoring
  // *********************************************
  if(function_exists('mb_substr')) {
    $firstchar = mb_substr($password, 0, 1);
  } else {
    $firstchar = substr($password, 0, 1);
  } // if

  $same = '0';
  for($i=0; $length>$i; $i++) {
    if(function_exists('mb_substr')) {
      $char = mb_substr($password, $i, 1);
      } else {
      $char = substr($password, $i, 1);
    } // if
    if($firstchar == $char) {
      $same = $same + 1;
    }
    else {
      $same = $same - 1;
    }
  } // for

  if($same == $length) {
    $score = $score - 3;
    $score = number_format($score, '1');
    $msg[] = 'Password is all the same character';
  }

  // *********************************************
  // create a password array of unique chars
  // *********************************************
  for($i=0; $length>$i; $i++) {

    // extract a char from the password
    if(function_exists('mb_substr')) {
      $char = mb_substr($password, $i, 1);
      } else {
      $char = substr($password, $i, 1);
    } // if

    // if it is the first char, put it in the array
    if($i == '0') {
      $pass_array[] = $char;
    }

    // if it is not the first char
    if($i !== '0') {
      $count = count($pass_array);
      $there = '0';

      // check if the char already exists in pass_array
      for($c=0; $count>$c; $c++) {
        if($char == $pass_array[$c]) {
          $there = $there + 1;
        }
      } // for c

      // if the char was not found in the array, put it in
      if($there == '0') {
        $pass_array[] = $char;
      }
    } // $i !== 0

  } // create an array of unique chars

  // *********************************************
  // tally occurances of the same char
  // *********************************************
  // get the number of times each char
  // in the pass_array occurs in the password
  $count = count($pass_array);
  for($c=0; $count>$c; $c++) {
    $get = $pass_array[$c];
    $tally = '0';

    for($i=0; $length>$i; $i++) {
      // extract a char from the password
      if(function_exists('mb_substr')) {
        $char = mb_substr($password, $i, 1);
        } else {
        $char = substr($password, $i, 1);
      } // if

      if($get == $char) {
        $tally = $tally + 1;
      }
    } // for i

    $occur_array[$c] = $tally;
  } // for occur



  // *********************************************
  // penalize for too many of the same char
  // *********************************************

  $count = count($occur_array);
  if($length > 3) {
    for($c=0; $count>$c; $c++) {
      $gettally = array_shift($occur_array);

      $div = $gettally / $length;
      $norm = $div * 100;
      $round = ceil($norm);

      if($round >= 33 && 49 >= $round) {
        $score = $score - 1;
        $score = number_format($score, '1');
        $msg[] = 'At least one third of the characters are the same (' . $round . '%)';
      }
      if($round >= 50 && 74 >= $round) {
        $score = $score - 2.5;
        $score = number_format($score, '1');
        $msg[] = 'At least one half of the characters are the same (' . $round . '%)';
      }
      if($round >= 75 && 89 >= $round) {
        $score = $score - 3;
        $score = number_format($score, '1');
        $msg[] = 'At least three quarters of the characters are the same (' . $round . '%)';
      }
      if($round >= 90 && 100 >= $round) {
        $score = $score - 3.5;
        $score = number_format($score, '1');
        $msg[] = 'At least 90% of the characters are the same (' . $round . '%)';
      }
    } // for c
  } // !== 3


  // *********************************************
  // length scoring
  // *********************************************

  // password is less than 8 characters message
  if(8 > $length) {
    $msg[] = 'Password is less than 8 characters';
  }

  // 1 to 3
  if(4 > $length) {
    $score = $score - 1;
    $score = number_format($score, '1');
  }

  // 4 to 6
  if(7 > $length && $length >= 4) {
    $score = $score - 0.5;
    $score = number_format($score, '1');
  }

  // 8 to 9
  if(10 > $length && $length >= 8) {
    $score = $score + 0.3;
    $score = number_format($score, '1');
  }

  // 10 to 12
  if(13 > $length && $length >= 10) {
    $score = $score + 0.8;
    $score = number_format($score, '1');
  }

  // 13 to 15
  if(16 > $length && $length >= 13) {
    $score = $score + 1.5;
    $score = number_format($score, '1');
  }

  // length scoring maxes out at 16 characters
  if($length >= 16) {
    $score = $score + 2;
    $score = number_format($score, '1');
  }

  if($length >= 20) {
    $score = $score + 4;
    $score = number_format($score, '1');
    $msg[] = 'The password is more than 20 characters';
  }


  // *********************************************
  // check scoring
  // *********************************************
  if($score == '0') {
    $score = 0.05;
    $score = number_format($score, '1');
  }

  if($score < '0') {
    $score = 0.05;
    $score = number_format($score, '1');
  }

  // this means the max score can be five
  if($score > 5) {
    $score = 5;
    $score = number_format($score, '1');
  }

  if($password == '') {
    $score = 0;
    $score = number_format($score, '1');
  }

  // normalize the score to 100
  $score = $score * 20;
  $score = number_format($score, '0');


  // *********************************************
  // assessment messages
  // *********************************************
  if($score >= 100) {
    $ass = 'Fantastic!';
  }

  if(100 > $score && $score >= 90) {
    $ass = 'Excellent!';
  }

  if(90 > $score && $score >= 80) {
    $ass = 'Good';
  }

  if(80 > $score && $score >= 70) {
    $ass = 'Average';
  }

  if(70 > $score && $score >= 60) {
    $ass = 'Mediocre';
  }

  if(60 > $score && $score >= 50) {
    $ass = 'Poor';
  }

  if(50 > $score && $score >= 40) {
    $ass = 'Lame';
  }

  if(40 > $score && $score >= 30) {
    $ass = 'Awful';
  }

  if(30 > $score && $score >= 20) {
    $ass = 'Appalling';
  }

  if(20 > $score && $score >= 0) {
    $ass = 'You want to be hacked';
  }

  if($password == '' && $score == '0') {
    $ass = '';
  }

  if($msg == '') {
    $msg[] = 'No message';
  }


  $result[] = $password;
  $result[] = $score;
  $result[] = $ass;
  $result[] = $msg;
  $result[] = $pass_array;
  $result[] = $occur_array;

  return $result;
// **************************************************************
} // Close pass_strength function
// **************************************************************
?>
