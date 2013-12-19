<?php
//Check if this is called from the application
if(!defined('SPF'))
{
	header('Location:/');
	exit();
}

	if (($Auth->loggedIn()) AND ($Auth->isAdmin()))
	{
?>

	<div class="pull-right">
		<a href="blog_edit.php" class="btn btn-default btn-lg">
			Create New Blog <?php echo icon("plus"); ?>
		</a>
	</div>


<?php }

if ($Pager) echo $Pager;
?>

<hr />

<?php
	$c = 1;
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

		$style = "border-bottom: 1px solid #ccc; padding-bottom: 10px; padding-top: 10px;";
		if ($c %2 == 0)
		{
			$style .= "background: #f7fafb;";
		}

?>
	<div class="row" id="<?php echo $id; ?>" style="margin-left: -15px; margin-right: -15px;<?php echo $style; ?>">
		<div class="col-sm-2">
			<div align="left" style="padding-top: 20px;">
				<div class="pull-left">

					<div class="blogBookmark">
						<?php echo icon('bookmark') ?>
					</div>

					<div align="center" class="blogDate">
						<b><?php echo dater($blog['timestamp'], "d"); ?></b>
					</div>

				</div>

				<div align="left" class="blogMonth">
					&nbsp;<?php echo dater($blog['timestamp'], "M"); ?>
				</div>

				<div align="left" style="blogTime">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo dater($blog['timestamp'], "G:i"); ?>
				</div>

				<div class="clearfix"></div>

				<div align="left" class="blogAuthor">
					&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span>Written by <b><?php echo $username; ?></b></span>
				</div>
			</div>
		</div>
		<div class="col-sm-7">
			<div>

<?php
	if (($admin)
	or ($Auth->id == $u->id))
	{
?>
					<div class="btn-group pull-right">
						<a class="btn btn-default" href="blog_edit.php?blog=<?php echo $id; ?>">
							<i class="glyphicon glyphicon-pencil"></i>
						</a>
						<a class="btn btn-danger confirm" href="blog.php?action=remove&blog=<?php echo $id; ?>">
							<i class="glyphicon glyphicon-trash"></i>
						</a>
					</div>
<?php
	}
?>
				<h3>
					<b><?php echo $blog['desc']; ?></b>
					<br />
					<small>
						<span><?php echo time2str($blog['timestamp']);  ?></span>
					</small>
				</h3>
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


		<div class="col-sm-3">
			<h2>
				<small><?php echo $count_comments; ?> Comments</small>
			</h2>


<?php
// Check if ther are comments and then display them
	if ($comments)
	{
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
			<form method="post" class="form">
				<div class="form-group">
					<label class="control-label" for="input01">Something to say?</label>
					<textarea rows="2" class="form-control" id="input01" name="comment" placeholder="Say something"></textarea>
				</div>
				<input type="submit" class="form-control btn btn-default btn-primary" name="action" value="Comment" />
				<input type="hidden" name="blog" value="<?php echo $id; ?>" />
			</form>
<?php 	}	?>

		</div>

	<!-- Close the blog -->
	</div>

<?php
	$c++;
	}
?>

<?php
	if ($Pager) echo $Pager;
?>