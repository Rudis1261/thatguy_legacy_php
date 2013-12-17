<?php
//Check if this is called from the application
if(!defined('SPF'))
{
    header('Location:/');
    exit();
}
?>
<form class="form-inline" role="form">
    <div class="form-group">
        <input name="search" type="text" class="form-control" placeholder='Username' value="<?php echo $search; ?>">
    </div>
    <button type="submit" class="btn btn-default btn-primary">Search</button>
    <input name="page" type="hidden" value="<?php echo $page; ?>" />
</form>

<?php
if ($total !== 0)
{
    if (isset($pager)) { echo $pager; } // Print Pager
    echo "<p>Users in total: <b>" . $total . "</b></p>".nl(); // User count

    if ($update)
    {
        echo '<div class="container">
		<div class="alert alert-info">' . $update . '</div>
	</div>';
    }

?>

<table class="table table-bordered table-striped">

<?php
    // If the user is an admin
    if ($admin==true)
    {
?>
  <thead>
      <tr>
        <th>Username</th>
        <th>Email</th>
    	<th>Group</th>
    	<th>Password</th>
    	<th>Activated</th>
      </tr>
    </thead>
    <tbody>
<?php
	foreach($users as $id=>$values)
	{
	    echo "<tr>". nl();
	    echo "<td>" . $values['username'] . "</td>". nl();

	    // Build the miniForm to update the user's email address
	    $string = "<input type='text' class='form-control input-sm' name='email' value='" . $values['email'] . "' />";
	    $string .= "<input type='hidden' name='action' value='update-email' />";
	    echo "<td>". miniform($string, $id, 'user_id', 'users_update_email') . "</td>". nl();

	    // Build the miniForm to update the user's email address
	    $string =  select('group', Users::types(), $values['group'], 'input-sm');
	    $string .= "<input type='hidden' name='action' value='update-group' />";
	    echo "<td>" . miniform($string, $id, 'user_id', 'users_update_group') . "</td>". nl();

	    // Build the miniForm to update the user's email address
	    $string = "<input type='password' class='form-control input-sm' name='password' value='password' />";
	    $string .= "<input type='hidden' name='action' value='update-password' />";
	    echo "<td>". miniform($string, $id, 'user_id', 'users_update_password') . "</td>". nl();
	    /*echo "<td>" . Options::userGet($id, 'firstName') . "</td>". nl();*/

	    // Build the miniForm to update the user's email address
	    $string = bool_select(Activation::status($id), 'active');
	    $string .= "<input type='hidden' name='action' value='update-activation' />";
	    echo "<td>". miniform($string, $id, 'user_id', 'users_update_activation') . "</td>". nl();
	    /*echo "<td>" . Options::userGet($id, 'firstName') . "</td>". nl();*/
	    echo "</tr>". nl();
	}
    }
?>

<?php
    // If the user is just a normal user
    if ($admin==false)
    {
?>
  <thead>
      <tr>
        <th>Username</th>
        <th>Send Email</th>
	<th>Group</th>
	<th>First Name</th>
	<th>Surname</th>
	<th>Birthdate</th>
      </tr>
    </thead>
    <tbody>
<?php
	foreach($users as $id=>$values)
	{
	    echo "<tr>".nl();
	    echo "<td>" . $values['username'] . "</td>".nl();
	    echo "<td><a class='btn btn-default' href='mailto:" . $values['email'] . "?subject=Hi There'>Send Email</i></a></td>".nl();
	    echo "<td>" . $values['group'] . "</td>".nl();
	    echo "<td>" . Options::userGet($id, 'firstName') . "</td>".nl();
	    echo "<td>" . Options::userGet($id, 'surname') . "</td>".nl();
	    $dob = (Options::userExists($id, 'dateOfBirth')) ? time2str(Options::userGet($id, 'dateOfBirth')) : '';
	    echo "<td>" . $dob . "</td>".nl();
	    echo "</tr>".nl();
	}
    }
?>

    </tbody>
  </table>

<?php

    if (isset($pager)) { echo $pager; } // Print Pager
} else {
    echo "<p><strong>Sorry no users found</strong></p>".nl(); // No users based on search criteria
}
?>