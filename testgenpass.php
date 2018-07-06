<?php
include("genpass.php");
include("passwordstrength.php");

//*******************************************************************************************************************
// Define initial variables
//*******************************************************************************************************************

if(isset($_POST['retest'])) {

  $password = $_POST['password'];
  $password = trim($password);
  $password = ($password == '') ? 'N/A' : $password;
  $pass_strength = pass_strength($password);
  $retestlength = strlen($password);

  $result['0'] = $_POST['len'];
  $result['1'] = $password;
  $result['2'] = $_POST['nums'];
  $result['3'] = $_POST['lower'];
  $result['4'] = $_POST['upper'];
  $result['5'] = $_POST['spcl'];
  $uniq        = $_POST['uniq'];
}

else {
  if(isset($_POST['generate'])) {

    $len    = $_POST['len'];
    $nums   = $_POST['nums'];
    $lower  = $_POST['lower'];
    $upper  = $_POST['upper'];
    $spcl   = $_POST['spcl'];
    $uniq   = $_POST['uniq'];

    $result = genPass($len, $nums, $lower, $upper, $spcl, $uniq);
    $pass_strength = pass_strength($result['1']);
  }

  else {
    // default values on page load
    $len = "15";
    $nums = "4";
    $lower = "5";
    $upper = "4";
    $spcl = "2";
    $uniq = "1";

    $result = genPass($len, $nums, $lower, $upper, $spcl, $uniq);
    $pass_strength = pass_strength($result['1']);
  }
}

//*******************************************************************************************************************
// Begin Page Proper
//*******************************************************************************************************************
?>

<html>
<head>
  <title>Generate Password</title>
</head>

<body style="background-color: lightgray; font-family: Arial;">

<form method="post" action="testgenpass.php">
<table border="1" cellpadding="5" cellspacing="0" style="margin: 0 auto; width: 750px;">
<tr>
<td colspan="2">Generate Password</td>
</tr>

<tr>
<td align="right" style="width: 110px;"><label for="labellen">Overall Length</label></td>
<td><input type="text" name="len" id="labellen" value="<?php echo $result[0]; ?>" style="width: 40px; text-align: left;" />&nbsp;&nbsp;<p style="display: inline; margin: 0; padding: 0; font-size: 9pt;"></p></td>
</tr>

<tr>
<td align="right">Must have</td>
<td><input type="text" name="nums" id="labelnums" value="<?php echo $result['2']; ?>" style="width: 20px; margin-right: 5px; text-align: center;" /><label for="labelnums">Numbers</label>&nbsp;&nbsp;<input type="text" name="lower" id="labellower" value="<?php echo $result['3']; ?>" style="width: 20px; margin-right: 5px; text-align: center;" /><label for="labellower">Lower case letters</label>&nbsp;&nbsp;<input type="text" name="upper" id="labelupper" value="<?php echo $result['4']; ?>" style="width: 20px; margin-right: 5px; text-align: center;" /><label for="labelupper">Upper case letters</label>&nbsp;&nbsp;<input type="text" name="spcl" id="labelspcl" value="<?php echo $result['5']; ?>" style="width: 20px; margin-right: 5px; text-align: center;" /><label for="labelspcl">Special characters</label></td>
</tr>

<tr>
<td align="right">Unique?</td>
<td><input type="radio" name="uniq" id="labeluniqyes" value="1" <?php if($uniq == '1' || $uniq == null) { echo "checked"; } ?> /><label for="labeluniqyes">&nbsp;Yes</label>&nbsp;<input type="radio" name="uniq" id="labeluniqno" value="0" <?php if($uniq == '0') { echo "checked"; } ?> /><label for="labeluniqno">&nbsp;No</label>&nbsp;&nbsp;&nbsp;(force characters to be unique)</td>
</tr>

<tr>
<td colspan="2" style="text-align: center; font-size: 9pt;">Note: ALL characters are included unless specified otherwise! You must enter 0 to exclude a specific character type.<br />(ie: entering 0 for Numbers will exclude numbers from the password)</td>
</tr>

<tr>
<td colspan="2" align="center"><input type="submit" name="generate" value="Generate Password" /><input type="submit" name="reset" value="Reset" style="margin-left: 20px;" /></td>
</tr>

<tr>
<td align="right" style="width: 70px;">Password</td>
<td><input type="text" name="password" value="<?php echo htmlentities($pass_strength['0']); ?>" /><?php if(isset($_POST['retest'])) { if($retestlength != $result['0']) { echo "&nbsp;&nbsp;Length: " . $retestlength; } } ?></td>
</tr>

<tr>
<td align="right" valign="top">Strength</td>
<td>
<div style="width: 100px; height: 15px; background-color: darkgray; border: 1px solid black; margin: 0 10px 0 0; padding: 0; float: left;">
<div style="width: <?php echo $pass_strength['1']; ?>px; height: 15px; background-color: seagreen; margin: 0; padding: 0; float: left; border-right: 1px solid black;">
</div>
</div>
<?php echo $pass_strength['1']."%"; ?>
<input type="submit" name="retest" value="Calculate Password Strength" style="display: inline; margin: 0 0 0 30px;" />
<p style="margin: 5px 0 0 0; padding: 0; font-weight: bold; display: block;"><?php echo $pass_strength['2']; ?></p>
</td>
</tr>

<tr>
<td colspan="2" align="center" style="color: maroon; font-weight: bold; font-size: 10pt;">
<?php
if($pass_strength['3'] !== null) {
  foreach($pass_strength['3'] as $value) {
    echo $value."<br />";
  }
}
?>
</td>
</tr>

<tr>
<td align="right" valign="top">Result</td>
<td><div style="width: 550px; word-wrap: break-word; padding: 0; margin: 0;"><?php echo htmlentities($result[1]); ?></div></td>
</tr>
</table>
</form>

</body>
</html>
