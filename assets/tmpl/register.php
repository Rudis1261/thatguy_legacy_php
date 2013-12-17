<?php
//Check if this is called from the application
if(!defined('SPF'))
{
	header('Location:/');
	exit();

}

if (((!empty($errorClass))
    and (!empty($errorClass)))
    and ($complete == false))
{
?>
<form method='post' action=''>
	<input type='hidden' name='callback' value='<?php echo $callback; ?>' />
	<div class="form-group <?php echo $errorClass[0]; ?>">
		<label class="control-label" for="input01">Username</label>
		<input type="text" class="form-control input-sm" id="input01" placeholder="Username" name="register-username" value="<?php echo $inputValue[0]; ?>">
	</div>

	<div class="form-group <?php echo $errorClass[1]; ?>">
		<label class="control-label" for="input02">Password</label>
		<input type="password" class="form-control input-sm" id="input02" placeholder="Password" name="register-password" value="<?php echo $inputValue[1]; ?>">
	</div>

	<div class="form-group <?php echo $errorClass[2]; ?>">
		<label class="control-label" for="input03">Confirm Password</label>
		<input type="password" class="form-control input-sm" id="input03" placeholder="Password" name="register-confirm" value="<?php echo $inputValue[2]; ?>">
	</div>

	<div class="form-group <?php echo $errorClass[3]; ?>">
		<label class="control-label" for="input04">Email Address</label>
		<input type="text" class="form-control input-sm " placeholder="Email@address.com" id="input04" name="register-email" value="<?php echo $inputValue[3]; ?>">
	</div>

	<div class="form-group <?php echo $errorClass[4]; ?>">
		<label class="control-label" for="input05">Comfirm that you are human</label>
	</div>

	<div class="form-group">
		<img src="image.php?action=captcha" class="img-rounded" style="border: 1px solid #ccc;" alt="Error, unable to load" />
	</div>

	<div class="form-group <?php echo $errorClass[4]; ?>">
		<input type="text" class="form-control input-sm " placeholder="Enter the code you see above (In lower case)" id="input05" name="register-captcha" value="<?php echo $inputValue[4]; ?>">
	</div>

	<div class="controls">
		<button type="submit" name="action" value='register' class="btn btn-default btn-lg btn-primary">Register</button>
	</div>
</form>
<?php
// End of main control
} else {
?>
<div class="alert alert-info">
	<h4>You have sucessfully registered, please check your email for the activation link.</h4>
</div>
<div style="clear: both;"></div>
<?php
}
?>