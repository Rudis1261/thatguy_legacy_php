<?php
//Check if this is called from the application
if(!defined('SPF'))
{
	header('Location:/');
	exit();
}
$execTime = '';
if (isset($time_start))
{
	$time_end = microtime_float();
	$time = $time_end - $time_start;
	$execTime = "brought to you in " . round($time, 2) . " seconds";
	$db = Database::getDatabase();
	$numSqlQueries 		= "";
	$additionalContent 	= "";
}

# Fire up the auth class
$Auth = Auth::getAuth();
if ( ($Auth->loggedIn()) && $Auth->isAdmin() )
{
	$numSqlQueries = " with " . count($db->queries) . " SQL queries.";
	$additionalContent = "<p>
							<b>Hey Admin, here's the SQL queries run.</b>
						</p>
						<p>
							<textarea class='input input-default input-sm col-lg-12' rows='10'>";
								foreach($db->queries as $sql)
								{
									$additionalContent .= $sql . "\n";
								}
								$additionalContent .= "
							</textarea>
						</p>";
}

?>
	<hr />
	<footer>
		<center >
			<h5>
				Proudly hosted at <a href="http://go.afrihost.com/stephanusroelof-strydom-hosting">Afrihost.com</a>. &lt;3 You guys!
				<?php echo Options::get('siteName'); ?>
				<small><?php echo $execTime; echo $numSqlQueries; ?>&nbsp;</small>
			</h5>

			<p>Special thanks to <a href='http://twitter.github.com/bootstrap/index.html' target="_blank">Bootstrap from Twitter</a> and the equally awesome
				<a href='https://github.com/tylerhall/simple-php-framework/' target="_blank">Simple PHP Framework</a>.
			</p>
			<?php echo $additionalContent; ?>
		</center>
	</footer>

    </div><!--.row-container-->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="<?php
	if (isset($javascript))
	{
		echo $javascript . "?t=" .  Cache::modified($javascript);
	}

	else
	{
		require DOC_ROOT . '/includes/javascript.inc.php';
		$JS->output('default.js');
		$JS->export();
		echo $JS->output() . "?t=" .  Cache::modified($JS->output());
	}

    ?>"></script>
  </body>
</html>