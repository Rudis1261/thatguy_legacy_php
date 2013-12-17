<?php
//Check if this is called from the application
if(!defined('SPF'))
{
	header('Location:/');
	exit();
}

$action = ($id) ? "Save Changes" : "Create new blog";
?>

<form class="form" id="myForm" method="post" enctype="multipart/form-data">
	<div class="form-group <?php echo $errorClass['desc']; ?>">
		<label class="control-label" for="input02">Blog Description</label>
		<input type="text" name="desc" tabindex="1" id="input01" class="input-sm focus form-control" placeholder="Give us a clue then" value="<?php echo $inputValue['desc']; ?>"/>
		</div>

	<input type="hidden" name="id" value="<?php echo $inputValue['id']; ?>" />

	<div class="form-group <?php echo $errorClass['text']; ?>">
		<label class="control-label" for="input01">Blog Content</label>
	</div>

	<div class="form-group <?php echo $errorClass['text']; ?>">
			<div class="btn-group form-group">
				<?php
					foreach(BBCode::showAll() as $type=>$bbcode)
					{
						echo nl().tab(7) . "<a href='#' title='Insert the " . $bbcode['code'] . " BBCode' class='bbcode btn btn-default'>" . nl();
						echo tab(8) . "<i class='pre hideMe'>" . $bbcode['pre'] . "</i>" . nl();
						echo tab(8) . "<i class='post hideMe'>" . $bbcode['post'] . "</i>" . nl();
						echo tab(8) . "<i class='" . $bbcode['icon'] . "'></i></a>" . nl();
					}
				?>
			</div>
		<textarea name="text" tabindex="2" id="text" rows="20" id="input01" class="form-control input-sm" placeholder="Well hello there"><?php echo $inputValue['text']; ?></textarea>
	</div>


	<?php
		if ((isset($uploads))
		&& (is_array($uploads))
		&& (count($uploads)>=1))
		{

	?>
  		<div class="form-group" id="showImages">
			<?php
				if (!empty($uploads))
				{
					foreach($uploads as $upload)
					{
						echo "<div class='col-sm-2'>
								<a href='#' style='background: white;'>
									<img class='insertImage img-thumbnail img-responsive' src='" .  $upload . "' alt='" . $upload . "' />
								</a>
								<a class='btn btn-default btn-xs btn-block btn-danger removeBlogImage' data-src='" . $upload . "' href='#'>Delete</a>
							</div>";
					}
				}
			?>
		</div>
		<div class="clearfix"></div>
	<?php
		}
	?>

	<div class="form-group">
		<label class="control-label">Upload images</label>
		<input type="file" multiple name="upload[]" id="blog_upload" />
	</div>

	<div class="form-group">
		<div class="controls col-lg-6" id="showImages"> </div>
	</div>
	<div class="clearfix"></div>
	<input type="submit" name="action" class="btn btn-default btn-lg btn-primary" value="<?php echo $action; ?>">
</form>