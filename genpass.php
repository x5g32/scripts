<?php
// ****************************************************************************************
// Generate Password Function
// ****************************************************************************************
// Result is returned as an array as follows:
// $result[0] = length of generated password
// $result[1] = the generated password itself
// $result[2] = number of user submitted numbers
// $result[3] = number of user submitted lowercase letters
// $result[4] = number of user submitted uppercase letters
// $result[5] = number of user submitted special characters
// ****************************************************************************************
function genPass($len='10', $nums=null, $lower=null, $upper=null, $spcl=null, $uniq='1') {

  if($nums == '0' && $lower == '0' && $upper == '0' && $spcl == '0') {
    $nums = null;
    $lower = null;
    $upper = null;
    $spcl = null;
  }

  // if the input was anything other than a number, make the variable null
  if(is_numeric($nums) == false) {
    $nums = null;
  }
  if(is_numeric($lower) == false) {
    $lower = null;
  }
  if(is_numeric($upper) == false) {
    $upper = null;
  }
  if(is_numeric($spcl) == false) {
    $spcl = null;
  }

  // add up all the input values, this is the minimum amount of required characters
  $inputlen = $nums + $lower + $upper + $spcl;

  // if all the input values were not a number or null, the input length is 0
  if($inputlen == null) {
    $inputlen = '0';
  }

  if(is_numeric($len) == false || $len == null) {
    $len = '0';
  }

  if(is_numeric($inputlen) == true && is_numeric($len) == true && $inputlen >= $len) {
    $len = $inputlen;
  }

  if($len == '0' && $inputlen == '0') {
    $len = '10';
  }

  if($uniq !== '1') {
    $uniq = '0';
  }


  // *************************************************************************
  // Character sets used for user declared $nums, $lower, $upper, $spcl
  // *************************************************************************
  $numref      = array('1','2','3','4','5','6','7','8','9','0');
  $lowerref    = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z');
  $upperref    = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
  $spclref     = array('~', '!', '@', '#', '$', '%', '^', '&', '*', '(', ')', '_', '+', '-', '=', '{', '}', ':', ';', '?', '>', '<', '[', ']');

  $numreflen   = count($numref);
  $lowerreflen = count($lowerref);
  $upperreflen = count($upperref);
  $spclreflen  = count($spclref);


  // **********************************
  // Build the $allref[] array
  // **********************************
  $allref = array();

  if($nums !== '0') {
    $allref = array_merge($allref, $numref);
  }
  if($lower !== '0') {
    $allref = array_merge($allref, $lowerref);
  }
  if($upper !== '0') {
    $allref = array_merge($allref, $upperref);
  }
  if($spcl !== '0') {
    $allref = array_merge($allref, $spclref);
  }

  // this is the total number of possible unique characters that can be in the password
  $allreflen = count($allref);


  // *********************
  // Variables
  // *********************
  $result      = array();
  $res         = null;


  // *******************************************************************
  // Gather some numbers
  // *******************************************************************
  $numres = null;
  if($nums !== '0' && $nums !== null && is_numeric($nums) == true) {
    while($nums > strlen($numres)) {
      if($uniq == '1' && $numreflen > strlen($numres)) {
        $limit = $numreflen - 1;
        $getnum = $numref[mt_rand(0,$limit)];

        if(strpos($numres, $getnum) === false) {
          $numres .= $getnum;
        }
      } // if $uniq == 1

      else {
        $limit = $numreflen - 1;
        $getnum = $numref[mt_rand(0,$limit)];
        $numres .= $getnum;
      } // if $uniq !== 1
    } // while
  } // if



  // *******************************************************************
  // Gather some lower case letters
  // *******************************************************************
  $lowerres = null;
  if($lower !== '0' && $lower !== null && is_numeric($lower) == true) {
    while($lower > strlen($lowerres)) {
      if($uniq == '1' && $lowerreflen > strlen($lowerres)) {
        $limit = $lowerreflen - 1;
        $getlower = $lowerref[mt_rand(0,$limit)];

        if(strpos($lowerres, $getlower) === false) {
          $lowerres .= $getlower;
        }
      } // if $uniq == 1

      else {
        $limit = $lowerreflen - 1;
        $getlower = $lowerref[mt_rand(0,$limit)];
        $lowerres .= $getlower;
      } // if $uniq !== 1
    } // while
  } // if



  // *******************************************************************
  // Gather some upper case letters
  // *******************************************************************
  $upperres = null;
  if($upper !== '0' && $upper !== null && is_numeric($upper) == true) {
    while($upper > strlen($upperres)) {
      if($uniq == '1' && $upperreflen > strlen($upperres)) {
        $limit = $upperreflen - 1;
        $getupper = $upperref[mt_rand(0,$limit)];

        if(strpos($upperres, $getupper) === false) {
          $upperres .= $getupper;
        }
      } // if $uniq == 1

    else {
        $limit = $upperreflen - 1;
        $getupper = $upperref[mt_rand(0,$limit)];
        $upperres .= $getupper;
      } // if $uniq !== 1
    } // while
  } // if



  // *******************************************************************
  // Gather some special characters
  // *******************************************************************
  $spclres = null;
  if($spcl !== '0' && $spcl !== null && is_numeric($spcl) == true) {
    while($spcl > strlen($spclres)) {
      if($uniq == '1' && $spclreflen > strlen($spclres)) {
        $limit = $spclreflen - 1;
        $getspcl = $spclref[mt_rand(0,$limit)];

        if(strpos($spclres, $getspcl) === false) {
          $spclres .= $getspcl;
        }
      } // if $uniq == 1

      else {
        $limit = $spclreflen - 1;
        $getspcl = $spclref[mt_rand(0,$limit)];
        $spclres .= $getspcl;
      } // if $uniq !== 1
    } // while
  } // if



  // *******************************************************************
  // Compile all the gathered results into a final $res string
  // *******************************************************************
  $res .= $numres;
  $res .= $lowerres;
  $res .= $upperres;
  $res .= $spclres;



  // *******************************************************************
  // Add chars to $res if $res is < $len
  // *******************************************************************
  if($len !== '0' && $len !== null && is_numeric($len) == true) {
    while($len > strlen($res)) {
      if($uniq == '1' &&  $allreflen > strlen($res)) {
        $limit = $allreflen - 1;
        $getchar = $allref[mt_rand(0,$limit)];

        if(strpos($res, $getchar) === false) {
          $res .= $getchar;
        }
      } // if $uniq == 1

      else {
        $limit = $allreflen - 1;
        $getchar = $allref[mt_rand(0,$limit)];
        $res .= $getchar;
      } // if $uniq !== 1
    } // while
  } // if



  // **********************************
  // Return all results in an array
  // **********************************

  // shuffle up everything in the results string
  $res = str_shuffle($res);
  $len = strlen($res);

  $result[] = $len;
  $result[] = $res;
  $result[] = $nums;
  $result[] = $lower;
  $result[] = $upper;
  $result[] = $spcl;
  $result[] = $allref;

  return $result;


// ****************************************************************************************
} // Close generate password function
// ****************************************************************************************
?>
