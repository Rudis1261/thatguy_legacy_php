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
							<textarea class='form-control col-lg-12' rows='10'>";
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
		<center>

			<!-- Place this code where you want the badge to render. -->
			<a href="//plus.google.com/109627982720737965997?prsrc=3" rel="publisher" target="_top" style="text-decoration:none;" title="Find me on Google Plus">
				<img src="assets/img/blank.gif" class="googlePlus" alt="Google+" style="border:0;width:32px;height:32px;"/>
			</a>

			<a href="https://www.facebook.com/thatguy.co.za" target="_BLANK" style="text-decoration:none;" title="Find me on Facebook">
				<img src="assets/img/blank.gif" class="facebook" alt="Facebook" style="border:0;width:32px;height:32px;"/>
			</a>

			<a href="http://www.flickr.com/photos/thatguycoza/" target="_BLANK" style="text-decoration:none;" title="Check out my Flickr account">
				<img src="assets/img/blank.gif" class="flickr" alt="Flickr" style="border:0;width:32px;height:32px;"/>
			</a>

			<a href="http://rudis1261.deviantart.com/" target="_BLANK" style="text-decoration:none;" title="Check me out on Deviant Art">
				<img src="assets/img/blank.gif" class="da" alt="DeviantArt" style="border:0;width:32px;height:32px;"/>
			</a>

		</center>
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
		$JS->output('javascript.js');
		$JS->export();
		echo $JS->output() . "?t=" .  Cache::modified($JS->output());
	}

    ?>"></script>
  </body>
</html>