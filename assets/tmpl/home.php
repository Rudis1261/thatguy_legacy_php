<?php
//Check if this is called from the application
if(!defined('SPF'))
{
	header('Location:/');
	exit();
}
?>
<?php
	// Copied from BLOG
	$i = 1;
	foreach($blogs as $id=>$blog)
	{
		$u = new User($blog['user_id']);
		$offset = ((isset($_GET['offset'])) and (is_numeric($_GET['offset']))) ? $_GET['offset'] : 0;
		$current = ((isset($_GET['blog_id'])) and (is_numeric($_GET['blog_id']))) ? $_GET['blog_id'] : false;

		$min = 0;
		$max = 10;
		$limit = 10;

		if ($id == $current)
		{
			$min = $offset;
			$max = $offset + $limit;
		}

		$comments = Blog::comments_get($id);
		$count_comments = (Blog::comments_get($id) > 1) ? count(Blog::comments_get($id)) : 'No ';

		// Lets check if they have a first name set otherise display their username
		if (Options::userExists($blog['user_id'], 'firstName'))
		{
			$username = Options::userGet($blog['user_id'], 'firstName');
		} else {
			$username = $u->username;
		}

?>

<!-- Portfolio -->
<?php
/*
	if ($randomImages)
	{
		$GD = new GD();
		foreach($randomImages as $key=>$image)
		{
		    $GD->collageLoadFile($image['image']);
		}
		$GD->collage(Options::get('collageWidth'), Options::get('collageHeight'));
		$GD->saveAs('assets/img/collage.jpg');
	}
	*/
?>
	<!--<div style="margin-top: -35px;">
		<a href="portfolio.php" class="thumbnail">
			<img alt='collage' src='assets/img/collage.jpg' />
		</a>
	</div>-->
<div class="container">

	<div>
		<h1>
			<img src="<?php echo Options::get('siteLogo'); ?>" style="margin-left: 105px;" width="100" alt="logo" />
			<br />
			<?php echo (Options::get('prettyName')); ?>
		</h1>
		<p>
			Welcome to ThatGuy.co.za, where I showcase my Photographic and website development skills.
		</p>
		<p>
			Please check out my <a href='portfolio.php'>Portfolio</a> for all the images I have recently taken. Please also feel free to contact me, should you like to make use of my services
		</p>
		<br />
		<p>
			<small>Check out my pages for some more images</small><br />
			<a href="http://www.flickr.com/people/thatguycoza/"><img src='assets/img/flickr.jpg' alt='flickr' /></a>
			&nbsp;
			<a href="http://rudis1261.deviantart.com/"><img src='assets/img/da.jpg' alt='deviant art' /></a>
			&nbsp;
			<a href="https://www.facebook.com/thatguy.co.za"><img src='assets/img/fb.jpg' alt='facebook' /></a>
		</p>
	</div>
</div>
<hr />
<!-- Portfolio -->

<!-- Blog -->



<div class="page-header">
    <h1>Blog<small> checkout what I am thinking</small></h1>
</div>




<div class="container">
	<div class="row">
		<div class="col-lg-2" align="center">
			<h2>
				<small>
					<div align="center" style="background: url('assets/img/bookmark.jpg'); color: white; width: 64px; padding-top: 10px; height: 84px;">
						<span><?php echo dater($blog['timestamp'], "d"); ?></span>
						<br /><br />
						<span><?php echo dater($blog['timestamp'], "M"); ?></span>
					</div>
				</small>
			</h2>
			<h4>
				<small>
					<span><?php echo dater($blog['timestamp'], "G:i"); ?></span>
				</small>
			</h4>
			<h4>
				<small>
					<span>Written by <b><?php echo $username; ?></b></span>
				</small>
			</h4>

		</div>
		<div class="col-lg-6">
			<div>
				<h2>
					<u><?php echo $blog['desc']; ?></u>
					<small>
						<span>(<?php echo time2str($blog['timestamp']);  ?>)</span>
					</small>
				</h2>
				<br /><br />
				<p>
				<?php
					// Process the blog info
					$string = $blog['body'];
					$string = htmlentities($string);
					$string = nl2br($string);
					$string = BBCode::imagic($string);
					$string = BBCode::codify($string);
					$string = BBCode::mailify($string);
					$string = BBCode::decode($string);
					$string = BBCode::linkify($string);
					echo $string;
				 ?>
				</p>
			</div>
		</div>


		<div class="col-lg-4">
			<br /><br />
			<h2>
				<small><?php echo $count_comments; ?> Comments</small>
			</h2>


<?php
// Check if ther are comments and then display them
	if ($comments)
	{
		echo "<br />";
		$i = 0;
		foreach($comments as $cid=>$comment)
		{
			if (($i >= $min)
			&& ($i <= $max))
			{
				if (($admin)
				or ($Auth->id == $comment['user_id']))
				{
					$allowed = true;
					$adminClass = "class='editDiv'";
					$deleteLink = " <a class='btn btn-default btn-xs btn-danger'  href='?action=delete_comment&id=" . $cid . "'>Delete</a>";
				} else {
					$allowed = false;
					$adminClass = "";
					$deleteLink = '';
				}

				$poster = ($Auth->id == $comment['user_id']) ? "You" : $comment['username'];

				$string = htmlentities($comment['comment']);
				$string = nl2br($string);
				$string = BBCode::imagic($string);
				$string = BBCode::codify($string);
				$string = BBCode::mailify($string);
				$string = BBCode::decode($string);
				$string = BBCode::linkify($string);

				// Display the comment
				echo "<div " . $adminClass . " >";
				echo $string;
				echo "<br />";
				echo "<i style='font-size: 10px;'>(Posted by <b>" . $poster . "</b>, " . time2str($comment['timestamp']) . ")</i>" . $deleteLink . "<br /><hr /></div>";

				if ($allowed == true) // Check if it's the poster, or an admin and allow modification of the comment
				{
?>
					<form method='post' class='form editForm hideMe'>
						<textarea name='comment' rows='4' class='input-sm'><?php echo $comment['comment']; ?></textarea>
						<input type='hidden' name='id' value='<?php echo $cid; ?>' />
						<br />
						<input type='submit' class='btn btn-default' name='action' value='Edit Comment' />
					</form>
<?php
				}
			}
			$i++;
		}

		// Check if there are more results
		if ($count_comments > $max)
		{
			echo "<a href='?offset=" . $max . "&blog_id=" . $id . "' class='btn btn-default'>Show older comments</a>";
			echo "<br />";
		}
	}

	// Check if they are logged in to be able to comment
	if ($Auth->loggedIn())
	{
?>
			<br />
			<form method="post" class="form">
				<div class="control-group">
					<label class="control-label" for="input01">Something to say?</label>
					<div class="controls">
						<textarea rows="4" class="input-sm" id="input01" name="comment" placeholder="Say something"></textarea>
					</div>
					<div class="controls">
						<input type="hidden" name="blog" value="<?php echo $id; ?>" />
						<input type="submit" class="btn btn-default" name="action" value="Comment" />
					</div>
				</div>
			</form>
<?php 	}

	}?>

		</div>

	<!-- Close the blog -->
	</div>
</div>
	<!-- BLOG -->


<?php

if ($install)
{
	echo '<div class="alert alert-info">
		<strong>Update</strong>
		<br />
		Ran the installation script.</div>';
}

if ($update)
{
	echo '<div class="alert alert-info">
		<strong>Update</strong>
		<br />
		Ran the options script to update the options.</div>';
}

?>
