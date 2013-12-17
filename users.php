<?php
//Bootstrap SPF
require 'includes/master.inc.php';
$mustauth=true;
require 'includes/user.inc.php';

// Variables
$userList = array();
$title="Users <small>checkout the crew</small>";
$page = (isset($_GET['page'])) ? $_GET['page'] : 1;
$search = ((isset($_GET['search'])) && ($_GET['search'] !== '')) ? " WHERE username LIKE '%" . $_GET['search'] . "%'" : '';
$searchValue = ((isset($_GET['search'])) && ($_GET['search'] !== '')) ? $_GET['search'] : '';
$searchAppend = "&search=" . $searchValue;
$update = '';

/* Process miniForms *//*
foreach($_POST as $key=>$val)
{
    echo "POST: " . $key . "=" . $val ."<br />";
}*/
// Lets update the details getting posted to us
if ($isadmin == true)
{
    switch (@$_POST['action']) {
    case 'update-email':
        Auth::changeEmail($_POST['user_id'], $_POST['email']);
        message($_POST['user_id'], 'email address has been updated');
        break;
    case 'update-password':
        Auth::changePassword($_POST['user_id'], $_POST['password']);
        message($_POST['user_id'], 'password has been updated');
        break;
    case 'update-group':
        Auth::changeGroup($_POST['user_id'], $_POST['group']);
        message($_POST['user_id'], 'group has been updated');
        break;
    case 'update-activation':
        switch (@$_POST['active'])
        {
            case '0':
                Activation::deactivate($_POST['user_id']);
                message($_POST['user_id'], 'account has been de-activated');
                break;

            case '1';
                Activation::activate($_POST['user_id']);
                message($_POST['user_id'], 'account has been activated');
                break;
        }
        break;
    }
}

// Create the update message
function message($userID, $string)
{
    global $update;
    $u = new User($userID);
    $update = "<strong>UPDATE:</strong><br />" .nl();
    $update .= ucfirst($u->username) . "'s " . $string;
}



// Instantiate the pager
$Pager=new DBPager('Users', 'SELECT COUNT(id) FROM users' . $search, 'SELECT * FROM users' . $search, $page, 10, 100);

// Build the paging
$paging = '<ul class="pagination">'. nl();
$padding = 3;
for($i=1; $i<=$Pager->numPages; $i++)
{

    $min = $Pager->page - $padding;
    $max = $Pager->page + $padding;
    if ($i == 1){
        $paging .= '<li><a href="?page=' . $i . $searchAppend .'">&laquo;</a></li>'. nl();
    }

    if ($i == $Pager->page){
        $paging .= '<li class="active"><a href="#">' . $i . '</a></li>'. nl();
    } else if (($i >= $max) xor ($i > $min)) {
        $paging .= '<li><a href="?page=' . $i . $searchAppend . '">' . $i . '</a></li>'. nl();
    }

    if ($i == $Pager->numPages){
        $paging .= '<li><a href="?page=' . $Pager->numPages . $searchAppend . '">&raquo;</a></li>'. nl();
    }
}
$paging .='</ul>'. nl();
// Build the paging

// Build the user list array and pass it into the template
$Users = DBObject::glob('Users', 'SELECT * FROM users ' . $search . ' ORDER By username ASC' . $Pager->limits);
foreach($Users as $User)
{
    $userList[$User->id]['email'] = $User->email;
    $userList[$User->id]['username'] = $User->username;
    $userList[$User->id]['group'] = $User->level;
}
// Build the user list


Template::setBaseDir('./assets/tmpl');

$html = Template::loadTemplate('layout', array(
	'header'=>Template::loadTemplate('header', array('title'=>$title,'user'=>$user,'admin'=>$isadmin,'msg'=>$msg, 'selected'=>'users', 'Auth'=>$Auth)),
	'content'=>Template::loadTemplate('users', array('users'=>$userList, 'pager'=>$paging, 'search'=>$searchValue, 'total'=>$Pager->numRecords, 'user'=>$user, 'admin'=>$isadmin, 'page'=>$Pager->page, 'update'=>$update)),
	'footer'=>Template::loadTemplate('footer',array('time_start'=>$time_start))
));

echo $html;
?>