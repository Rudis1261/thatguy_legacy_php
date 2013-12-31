<?php
//Check if this is called from the application
if(!defined('SPF'))
{
	header('Location:/');
	exit();
}
?>
    </div><!--.row-container-->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="<?php
	if (isset($javascript))
	{
		echo $javascript . "?t=" .  Cache::modified($javascript);
	} else {
		require DOC_ROOT . '/includes/javascript.inc.php';
		$JS->output('javascript.js');
		$JS->export();
		echo $JS->output() . "?t=" .  Cache::modified($JS->output());
	}

    ?>"></script>
  </body>
</html>