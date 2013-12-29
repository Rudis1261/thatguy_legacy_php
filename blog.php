<?php
//Bootstrap SPF
require 'includes/master.inc.php';
require 'includes/user.inc.php';

// The person needs to be logged in or an admin in order to delete the post
if ($Auth->loggedIn())
{
    if (Blog::check_remove($Auth->id, $Auth->isAdmin()))
    {
	   redirect('blog.php');
    }

    // Check if a blog comment was added
    Blog::comments_add($Auth->id);
    Blog::comments_edit($Auth->id);
    Blog::comments_delete($Auth->id);
}

$inputValue     = array('desc'=>'', 'text'=>'', '', '', '', '', '', '', '');
$errorClass     = array('desc'=>'', 'text'=>'', '', '', '', '', '', '', '');
$title          ="Blog <small>sharing my thoughts</small>";

$JS->add('prettify.js');
$JS->output('blog.js');
$JS->export();

$CSS->add('blog.css');
$CSS->output('blog.css');
$CSS->export();

# Ensure we are an admin before hooking into the FB API
if ($Auth->loggedIn() AND $Auth->isAdmin())
{
    # Hoop into the FB api
    require("API/facebook.php");
}

// Variables
$blogList       = array();
$page           = (isset($_GET['page'])) ? $_GET['page'] : 1;
$search         = ((isset($_GET['search'])) && ($_GET['search'] !== '')) ? " WHERE `desc` LIKE '%" . $_GET['search'] . "%' or `body` LINK '%" . $_GET['search'] . "%'" : '';
$searchValue    = ((isset($_GET['search'])) && ($_GET['search'] !== '')) ? $_GET['search'] : '';
$searchAppend   = "&search=" . $searchValue;

// Instantiate the pager
$Pager=new DBPager('Blog', 'SELECT COUNT(id) FROM blog' . $search, 'SELECT * FROM blog' . $search, $page, 10, 100);

// Build the paging
$paging         = '<ul class="pagination">'. nl();
$padding        = 3;

for($i=1; $i<=$Pager->numPages; $i++)
{
    $min = $Pager->page - $padding;
    $max = $Pager->page + $padding;

    if ($i == 1)
    {
        $paging .= '<li><a href="?page=' . $i . $searchAppend .'">&laquo;</a></li>'. nl();
    }

    if ($i == $Pager->page)
    {
        $paging .= '<li class="active"><a href="#">' . $i . '</a></li>'. nl();
    }

    elseif (($i >= $max) xor ($i > $min))
    {
        $paging .= '<li><a href="?page=' . $i . $searchAppend . '">' . $i . '</a></li>'. nl();
    }

    if ($i == $Pager->numPages)
    {
        $paging .= '<li><a href="?page=' . $Pager->numPages . $searchAppend . '">&raquo;</a></li>'. nl();
    }
}
$paging .='</ul>'. nl();
// Build the paging

// Build the user list array and pass it into the template
$Blogs = DBObject::glob('Blog', 'SELECT * FROM blog ' . $search . ' ORDER By timestamp DESC' . $Pager->limits);
foreach($Blogs as $blog)
{
    $blogList[$blog->id]['desc']        = $blog->desc;
    $blogList[$blog->id]['body']        = $blog->body;
    $blogList[$blog->id]['timestamp']   = $blog->timestamp;
    $blogList[$blog->id]['user_id']     = $blog->user_id;
}

Template::setBaseDir('./assets/tmpl');
$html = Template::loadTemplate('layout', array(
	'header'=>Template::loadTemplate('header', array('title'=>$title,'user'=>$user,'admin'=>$isadmin,'msg'=>$msg, 'selected'=>'blog', 'Auth'=>$Auth, "CSS"=>$CSS->output())),
	'content'=>Template::loadTemplate('blog', array('errorClass'=>$errorClass, 'inputValue'=>$inputValue, 'Auth'=>$Auth, 'admin'=>$isadmin, 'Pager'=>$paging, 'blogs'=>$blogList)),
	'footer'=>Template::loadTemplate('footer',array('time_start'=>$time_start, 'javascript'=>$JS->output()))
));

echo $html;
?>