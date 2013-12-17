<?php
//Check if this is called from the application
if(!defined('SPF'))
{
	header('Location:/');
	exit();
}

// Processing the actual information sent through
if($complete == 'true')
{
?>
    <div class="alert alert-success">
	<h4 class="alert-heading">Success</h4>
	<p>Thank you for taking the time to contact us, we endevor to get back to you as soon as possible.</p>
    </div>
<?php
} else if ($complete == 'error') {
?>
    <div class="alert alert-danger">
	<h4 class="alert-heading">Error:</h4>
	<p>Unfortunately, we were not able to send you the contact email address.</p>
    </div>
<?php
} else {
?>
<div class="container">
	<div class="span">
		<form class="form form-horizontal well" action="contact.php" method="post">
			<fieldset>

				<div class="control-group <?php echo $errorClass[0]; ?>">
					<label class="control-label" for="input02">Your name</label>
					<div class="controls">
						<input type="text" class="input-sm" id="input02" placeholder="Jhonny Bravo" name="contact-email-name" value="<?php echo $inputValue[0]; ?>">
					</div>
				</div>

				<div class="control-group <?php echo $errorClass[1]; ?>">
					<label class="control-label" for="input01">Your Email Address</label>
					<div class="controls">
						<input type="text" class="input-sm" id="input01" placeholder="me@mysite.com" name="from-address" value="<?php echo $inputValue[1]; ?>">
					</div>
				</div>

				<div class="control-group <?php echo $errorClass[2]; ?>">
					<label class="control-label" for="input02">Message Body</label>
					<div class="controls">
						<textarea name="contact-email-message" rows="15" id="input01" class="input-sm" placeholder="Howdy there!"><?php echo trim($inputValue[2]); ?></textarea>
					</div>
				</div>

				<br />
				<div class="controls">
					<input type="submit" name="action" class="btn btn-default btn-lg btn-primary" value="Send Mail">
				</div>
			</fieldset>
		</form>
	</div>
</div>
<?php } ?>