<?php
//Check if this is called from the application
if(!defined('SPF'))
{
	header('Location:/');
	exit();
}

if ($complete)
{
// What to display on success
?>
	<div class="alert alert-info col-lg-6">
		<strong>Account activated.</strong>
		<br /><br />
		<p>
			<strong>Thank you, </strong>
			for taking the time to register an account with us. Feel free to login and get started.
		</p>
	</div>
	<div class='clearfix'></div>

<?php } else {
// What to display on failure
?>
	<form method='post'>
		<p>You can manually active your account by filling in the details below, or go to the email we sent you and active your account using the link.</p>
		<div class="form-group <?php echo $errorClass[0]; ?>">
			<input type="text" class='input-sm form-control' name="username" placeholder='Username' value='<?php echo $inputValue[0]; ?>'>
		</div>
		<div class="form-group <?php echo $errorClass[1]; ?>">
			<input type="text" class='input-sm form-control' name="code" placeholder='Activation Code' value='<?php echo $inputValue[1]; ?>'>
		</div>
		<div class="form-group">
			<input type="submit" class='btn btn-default btn-lg btn-primary' name='action' value='Activate'>
		</div>
	</form>


<?php } ?>