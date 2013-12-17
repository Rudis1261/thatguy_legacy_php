<?PHP

    function printr($var)
    {
        $output = print_r($var, true);
        $output = str_replace("\n", "<br>", $output);
        $output = str_replace(' ', '&nbsp;', $output);
        echo "<div style='font-family:courier;'>$output</div>" . nl();
    }


    # Convert an RGB value to hex
    function rgb2hex($rgb)
    {
        $hex = "#";
        $hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);

        return $hex; // returns the hex value including the number sign (#)
    }

    # Get the avarage color of the image
    function average_color($img)
    {
        # Check if the file exists, otherwise fail
        if (!file_exists($img))
        {
            new Exception("average_color() The image provided does not exist");
            return false;
        }

        # Load it into GD
        $GD = new GD($img);

        # Resize it
        $GD->resize(1,1);

        # Get the color of the first pixel
        $rgb = imagecolorat($GD->im, 0,0);
        $red   = ($rgb >> 16) & 0xFF;
        $green = ($rgb >> 8) & 0xFF;
        $blue  =  $rgb & 0xFF;

        # Get the color in hex form
        $get_color = rgb2hex(array($red, $green, $blue));

        # Return the HTML color
        return $get_color;
    }

    // Formats a given number of seconds into proper mm:ss format
    function format_time($seconds)
    {
        return floor($seconds / 60) . ':' . str_pad($seconds % 60, 2, '0');
    }

    // Given a string such as "comment_123" or "id_57", it returns the final, numeric id.
    function split_id($str)
    {
        return match('/[_-]([0-9]+)$/', $str, 1);
    }

    // Creates a friendly URL slug from a string
    function slugify($str)
    {
        $str = preg_replace('/[^a-zA-Z0-9 -]/', '', $str);
        $str = strtolower(str_replace(' ', '-', trim($str)));
        $str = preg_replace('/-+/', '-', $str);
        return $str;
    }

    // Computes the *full* URL of the current page (protocol, server, path, query parameters, etc)
    function full_url()
    {
        // Attempt to get the details from the browser, but if we are not able to, then default
        if (isset($_SERVER['HTTP_HOST']))
        {
            $s = (!isset($_SERVER['HTTPS'])) ? '' : ($_SERVER['HTTPS'] == 'on') ? 's' : '';
            $protocol = substr(strtolower($_SERVER['SERVER_PROTOCOL']), 0, strpos(strtolower($_SERVER['SERVER_PROTOCOL']), '/')) . $s;
            $port = ($_SERVER['SERVER_PORT'] == '80') ? '' : (":".$_SERVER['SERVER_PORT']);
            return $protocol . "://" . $_SERVER['HTTP_HOST'] . $port . $_SERVER['REQUEST_URI'];
        } else {
            return Options::get('siteName') . "/" . $stript_name;
        }
    }

    // Computes the *full* URL of the current page (protocol, server, path, query parameters, etc)
    function full_url_to_script($stript_name)
    {
        // Attempt to get the details from the browser, but if we are not able to, then default
        if (isset($_SERVER['HTTP_HOST']))
        {
            $s = (!isset($_SERVER['HTTPS'])) ? '' : ($_SERVER['HTTPS'] == 'on') ? 's' : '';
            $protocol = substr(strtolower($_SERVER['SERVER_PROTOCOL']), 0, strpos(strtolower($_SERVER['SERVER_PROTOCOL']), '/')) . $s;
            $port = ($_SERVER['SERVER_PORT'] == '80') ? '' : (":".$_SERVER['SERVER_PORT']);
            $path = substr($_SERVER['REQUEST_URI'], 0, (strrpos($_SERVER['REQUEST_URI'], '/')+1));
            return $protocol . "://" . $_SERVER['HTTP_HOST'] . $port . $path . $stript_name;
        } else {
            return Options::get('siteName') . "/" . $stript_name;
        }
    }

    // Get the script name and return it
    function script_name()
    {
        return substr($_SERVER['REQUEST_URI'], (strrpos($_SERVER['REQUEST_URI'], '/')+1) );
    }

    // Get the latest timestamp from a comma separetad list
    function list_time($timestampList)
    {
        $latest = (!is_null(max(explode(',', $timestampList)))) ? max(explode(',', $timestampList)) : $timestampList;
        return $latest;
    }

    // Returns an English representation of a date
    // Graciously stolen from http://ejohn.org/files/pretty.js
    function time2str($ts)
    {
        if(!ctype_digit($ts))
            $ts = strtotime($ts);

        $diff = time() - $ts;
        if($diff == 0)
            return 'now';
        elseif($diff > 0)
        {
            $day_diff = floor($diff / 86400);
            if($day_diff == 0)
            {
                if($diff < 60) return 'just now';
                if($diff < 120) return '1 minute ago';
                if($diff < 3600) return floor($diff / 60) . ' minutes ago';
                if($diff < 7200) return '1 hour ago';
                if($diff < 86400) return floor($diff / 3600) . ' hours ago';
            }
            if($day_diff == 1) return 'Yesterday';
            if($day_diff < 7) return $day_diff . ' days ago';
            if($day_diff < 31) return ceil($day_diff / 7) . ' weeks ago';
            if($day_diff < 60) return 'last month';
            $ret = date('F Y', $ts);
            return ($ret == 'December 1969') ? '' : $ret;
        }
        else
        {
            $diff = abs($diff);
            $day_diff = floor($diff / 86400);
            if($day_diff == 0)
            {
                if($diff < 120) return 'in a minute';
                if($diff < 3600) return 'in ' . floor($diff / 60) . ' minutes';
                if($diff < 7200) return 'in an hour';
                if($diff < 86400) return 'in ' . floor($diff / 3600) . ' hours';
            }
            if($day_diff == 1) return 'Tomorrow';
            if($day_diff < 4) return date('l', $ts);
            if($day_diff < 7 + (7 - date('w'))) return 'next week';
            if(ceil($day_diff / 7) < 4) return 'in ' . ceil($day_diff / 7) . ' weeks';
            if(date('n', $ts) == date('n') + 1) return 'next month';
            $ret = date('F Y', $ts);
            return ($ret == 'December 1969') ? '' : $ret;
        }
    }

    // Returns an array representation of the given calendar month.
    // The array values are timestamps which allow you to easily format
    // and manipulate the dates as needed.
    function calendar($month = null, $year = null)
    {
        if(is_null($month)) $month = date('n');
        if(is_null($year)) $year = date('Y');

        $first = mktime(0, 0, 0, $month, 1, $year);
        $last = mktime(23, 59, 59, $month, date('t', $first), $year);

        $start = $first - (86400 * date('w', $first));
        $stop = $last + (86400 * (7 - date('w', $first)));

        $out = array();
        while($start < $stop)
        {
            $week = array();
            if($start > $last) break;
            for($i = 0; $i < 7; $i++)
            {
                $week[$i] = $start;
                $start += 86400;
            }
            $out[] = $week;
        }

        return $out;
    }

    // Processes mod_rewrite URLs into key => value pairs
    // See .htacess for more info.
    function pick_off($grab_first = false, $sep = '/')
    {
        $ret = array();
        $arr = explode($sep, trim($_SERVER['REQUEST_URI'], $sep));
        if($grab_first) $ret[0] = array_shift($arr);
        while(count($arr) > 0)
            $ret[array_shift($arr)] = array_shift($arr);
        return (count($ret) > 0) ? $ret : false;
    }

    // Creates a list of <option>s from the given database table.
    // table name, column to use as value, column(s) to use as text, default value(s) to select (can accept an array of values), extra sql to limit results
    function get_options($table, $val, $text, $default = null, $sql = '')
    {
        $db = Database::getDatabase(true);
        $out = '';

        $table = $db->escape($table);
        $rows = $db->getRows("SELECT * FROM `$table` $sql");
        foreach($rows as $row)
        {
            $the_text = '';
            if(!is_array($text)) $text = array($text); // Allows you to concat multiple fields for display
            foreach($text as $t)
                $the_text .= $row[$t] . ' ';
            $the_text = htmlspecialchars(trim($the_text));

            if(!is_null($default) && $row[$val] == $default)
                $out .= '<option value="' . htmlspecialchars($row[$val], ENT_QUOTES) . '" selected="selected">' . $the_text . '</option>'.nl();
            elseif(is_array($default) && in_array($row[$val],$default))
                $out .= '<option value="' . htmlspecialchars($row[$val], ENT_QUOTES) . '" selected="selected">' . $the_text . '</option>'.nl();
            else
                $out .= '<option value="' . htmlspecialchars($row[$val], ENT_QUOTES) . '">' . $the_text . '</option>'.nl();
        }
        return $out;
    }

    // This function will conver camelCase to spaced capped words
    function camelcase2space($string)
    {
        $out = preg_replace('/(?<=\\w)(?=[A-Z])/'," $1", $string);
        $out = trim(ucwords($out));
        return $out;
    }


    // Creates a list of <option>s from the given database table.
    // table name, column to use as value, column(s) to use as text, default value(s) to select (can accept an array of values), extra sql to limit results
    function array2options($array, $default=null, $useKeys=false)
    {
        $out = '';

        foreach($array as $key=>$row)
        {
            $value = ($useKeys == false) ? $row : $key;
            if(!is_null($default) && $row == $default)
                $out .= tab() . tab() . '<option value="' . htmlspecialchars($value, ENT_QUOTES) . '" selected="selected">' . ucfirst($row) . '</option>'.nl();
            elseif(is_array($default) && in_array($row,$default))
                $out .= tab() . tab() . '<option value="' . htmlspecialchars($value, ENT_QUOTES) . '" selected="selected">' . ucfirst($row) . '</option>'.nl();
            else
                $out .= tab() . tab() . '<option value="' . htmlspecialchars($value, ENT_QUOTES) . '">' . ucfirst($row) . '</option>'.nl();
        }
        return $out;
    }

    // This select will create a select for you with the use of the array2options function
    function select($name, $array, $default=null, $class='', $useKeys=false, $multiple=false)
    {
        $multiple = ($multiple !== false) ? ' multiple ' : '';
        $out = tab() . "<select class='input-sm from-control " . $class . "' " . $multiple . " name='" . $name . "'>" . nl();
        $out .= array2options($array, $default, $useKeys);
        $out .= tab() . "</select>";
        return $out;
    }
    //select($name, $array, $default=null)
    //miniForm($string, $id, $name)
    // Dirty function to create a form per option to keep from things pretty"ier"
    function miniForm($string, $id, $name, $identifier='miniForm')
    {
    	$out = "<form class='form-inline " . $identifier . "' method='post'>".nl();
    	$out .= tab() . "<input class='form-control' name='" . $name . "' type='hidden' value='" . $id . "' />".nl();
    	$out .= $string . nl();
        $out .= tab() . "<input class='btn btn-default iHide' type='submit' value='Update' />".nl();
    	$out .= "</form>" . nl();
    	return $out;
    }

    // More robust strict date checking for string representations
    function chkdate($str)
    {
        // Requires PHP 5.2
        if(function_exists('date_parse'))
        {
            $info = date_parse($str);
            if($info !== false && $info['error_count'] == 0)
            {
                if(checkdate($info['month'], $info['day'], $info['year']))
                    return true;
            }

            return false;
        }

        // Else, for PHP < 5.2
        return strtotime($str);
    }

    // Converts a date/timestamp into the specified format
    function dater($date = null, $format = null)
    {
        if(is_null($format))
            $format = 'Y-m-d H:i:s';

        if(is_null($date))
            $date = time();

		if(is_int($date))
			return date($format, $date);
		if(is_float($date))
			return date($format, $date);
		if(is_string($date)) {
	        if(ctype_digit($date) === true)
	            return date($format, $date);
			if((preg_match('/[^0-9.]/', $date) == 0) && (substr_count($date, '.') <= 1))
				return date($format, floatval($date));
			return date($format, strtotime($date));
		}

		// If $date is anything else, you're doing something wrong,
		// so just let PHP error out however it wants.
		return date($format, $date);
    }

    // Formats a phone number as (xxx) xxx-xxxx or xxx-xxxx depending on the length.
    function format_phone($phone)
    {
        $phone = preg_replace("/[^0-9]/", '', $phone);

        if(strlen($phone) == 7)
            return preg_replace("/([0-9]{3})([0-9]{4})/", "$1-$2", $phone);
        elseif(strlen($phone) == 10)
            return preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "($1) $2-$3", $phone);
        else
            return $phone;
    }

    // Outputs hour, minute, am/pm dropdown boxes
    function hourmin($hid = 'hour', $mid = 'minute', $pid = 'ampm', $hval = null, $mval = null, $pval = null)
    {
        // Dumb hack to let you just pass in a timestamp instead
        if(func_num_args() == 1)
        {
            list($hval, $mval, $pval) = explode(' ', date('g i a', strtotime($hid)));
            $hid = 'hour';
            $mid = 'minute';
            $aid = 'ampm';
        }
        else
        {
            if(is_null($hval)) $hval = date('h');
            if(is_null($mval)) $mval = date('i');
            if(is_null($pval)) $pval = date('a');
        }

        $hours = array(12, 1, 2, 3, 4, 5, 6, 7, 9, 10, 11);
        $out = "<select name='$hid' id='$hid'>" . nl();
        foreach($hours as $hour)
            if(intval($hval) == intval($hour)) $out .= "<option value='$hour' selected>$hour</option>" . nl();
            else $out .= "<option value='$hour'>$hour</option>" . nl();
        $out .= "</select>" . nl();

        $minutes = array('00', 15, 30, 45);
        $out .= "<select name='$mid' id='$mid'>" . nl();
        foreach($minutes as $minute)
            if(intval($mval) == intval($minute)) $out .= "<option value='$minute' selected>$minute</option>" . nl();
            else $out .= "<option value='$minute'>$minute</option>" . nl();
        $out .= "</select>" . nl();

        $out .= "<select name='$pid' id='$pid'>" . nl();
        $out .= "<option value='am'>am</option>" . nl();
        if($pval == 'pm') $out .= "<option value='pm' selected>pm</option>" . nl();
        else $out .= "<option value='pm'>pm</option>" . nl();
        $out .= "</select>" . nl();
        return $out;
    }

    // Returns the HTML for a month, day, and year dropdown boxes.
    // You can set the default date by passing in a timestamp OR a parseable date string.
    // $prefix_ will be appened to the name/id's of each dropdown, allowing for multiple calls in the same form.
    // $output_format lets you specify which dropdowns appear and in what order.
    function mdy($date = null, $prefix = null, $output_format = 'm d y')
    {
        if(is_null($date)) $date = time();
        if(!ctype_digit($date)) $date = strtotime($date);
        if(!is_null($prefix)) $prefix .= '_';
        list($yval, $mval, $dval) = explode(' ', date('Y n j', $date));

        $month_dd = "<select class='input-sm from-control' name='{$prefix}month' id='{$prefix}month'>" . nl();
        for($i = 1; $i <= 12; $i++)
        {
            $selected = ($mval == $i) ? ' selected="selected"' : '';
            $month_dd .= "<option value='$i'$selected>" . date('F', mktime(0, 0, 0, $i, 1, 2000)) . "</option>" . nl();
        }
        $month_dd .= "</select>" . nl();

        $day_dd = "<select class='input-sm from-control' name='{$prefix}day' id='{$prefix}day'>" . nl();
        for($i = 1; $i <= 31; $i++)
        {
            $selected = ($dval == $i) ? ' selected="selected"' : '';
            $day_dd .= "<option value='$i'$selected>$i</option>" . nl();
        }
        $day_dd .= "</select>" . nl();

        $year_dd = "<select class='input-sm from-control' name='{$prefix}year' id='{$prefix}year'>" . nl();
        for($i = (date('Y')-100); $i < date('Y') + 10; $i++)
        {
            $selected = ($yval == $i) ? ' selected="selected"' : '';
            $year_dd .= "<option value='$i'$selected>$i</option>" . nl();
        }
        $year_dd .= "</select>" . nl();

        $trans  = array('m' => $month_dd, 'd' => $day_dd, 'y' => $year_dd);
        $result = strtr($output_format, $trans);
        return "<div class='clearfix'></div>" . $result;
    }

    // This function will create a bool_selection box with a true or false value
    // Takes 2 parameters, value and the name for post / get to be sent with
    function bool_select($value, $name, $class='', $addEmpty=false)
    {
        $selectedFalse = ((int)$value == False) ? " selected " : "";
        $selectedTrue = ((int)$value == True) ? " selected " : "";
        $selectedNone = ($value === "none") ? " selected " : "";

        $out = "<div class='clearfix'></div>";
        $out .= '<select class="input-sm from-control" name="' . $name . '">' . nl();
        $out .= ($addEmpty !== false) ? '<option ' . $selectedNone .  ' value="blank"></option>' . nl(): "";
        $out .= '   <option ' . $selectedFalse .  ' value="0">No</option>' . nl();
        $out .= '   <option ' . $selectedTrue . ' value="1">Yes</option>' . nl();
        $out .= '</select>'     . nl();
        return $out;
    }

    // Redirects user to $url
    function redirect($url = null)
    {
        if(is_null($url)) $url = $_SERVER['PHP_SELF'];
        header("Location: $url");
        exit();
    }

    // Ensures $str ends with a single /
    function slash($str)
    {
        return rtrim($str, '/') . '/';
    }

    // Ensures $str DOES NOT end with a /
    function unslash($str)
    {
        return rtrim($str, '/');
    }

    // Returns an array of the values of the specified column from a multi-dimensional array
    function gimme($arr, $key = null)
    {
        if(is_null($key))
            $key = current(array_keys($arr));

        $out = array();
        foreach($arr as $a)
            $out[] = $a[$key];

        return $out;
    }

    // Fixes MAGIC_QUOTES
    function fix_slashes($arr = '')
    {
        if(is_null($arr) || $arr == '') return null;
        if(!get_magic_quotes_gpc()) return $arr;
        return is_array($arr) ? array_map('fix_slashes', $arr) : stripslashes($arr);
    }

    // Returns the first $num words of $str
    function max_words($str, $num, $suffix = '')
    {
        $words = explode(' ', $str);
        if(count($words) < $num)
            return $str;
        else
            return implode(' ', array_slice($words, 0, $num)) . $suffix;
    }

    // Serves an external document for download as an HTTP attachment.
    function download_document($filename, $mimetype = 'application/octet-stream')
    {
        if(!file_exists($filename) || !is_readable($filename)) return false;
        $base = basename($filename);
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Disposition: attachment; filename=$base");
        header("Content-Length: " . filesize($filename));
        header("Content-Type: $mimetype");
        readfile($filename);
        exit();
    }

    // Retrieves the filesize of a remote file.
    function remote_filesize($url, $user = null, $pw = null)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_NOBODY, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        if(!is_null($user) && !is_null($pw))
        {
            $headers = array('Authorization: Basic ' .  base64_encode("$user:$pw"));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $head = curl_exec($ch);
        curl_close($ch);

        preg_match('/Content-Length:\s([0-9].+?)\s/', $head, $matches);

        return isset($matches[1]) ? $matches[1] : false;
    }

	// Inserts a string within another string at a specified location
	function str_insert($needle, $haystack, $location)
	{
	   $front = substr($haystack, 0, $location);
	   $back  = substr($haystack, $location);

	   return $front . $needle . $back;
	}

    // Outputs a filesize in human readable format.
    function bytes2str($val, $round = 0)
    {
        $unit = array('','K','M','G','T','P','E','Z','Y');
        while($val >= 1000)
        {
            $val /= 1024;
            array_shift($unit);
        }
        return round($val, $round) . array_shift($unit) . 'B';
    }

    // Tests for a valid email address and optionally tests for valid MX records, too.
    function valid_email($email, $test_mx = false)
    {
        if(preg_match("/^([_a-z0-9+-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $email))
        {
            if($test_mx)
            {
                list( , $domain) = explode("@", $email);
                return getmxrr($domain, $mxrecords);
            }
            else
                return true;
        }
        else
            return false;
    }

    // Grabs the contents of a remote URL. Can perform basic authentication if un/pw are provided.
    function geturl($url, $username = null, $password = null)
    {
        if(function_exists('curl_init'))
        {
            $ch = curl_init();
            if(!is_null($username) && !is_null($password))
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Basic ' .  base64_encode("$username:$password")));
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            $html = curl_exec($ch);
            curl_close($ch);
            return $html;
        }
        elseif(ini_get('allow_url_fopen') == true)
        {
            if(!is_null($username) && !is_null($password))
                $url = str_replace("://", "://$username:$password@", $url);
            $html = file_get_contents($url);
            return $html;
        }
        else
        {
            // Cannot open url. Either install curl-php or set allow_url_fopen = true in php.ini
            return false;
        }
    }

    // Returns the user's browser info.
    // browscap.ini must be available for this to work.
    // See the PHP manual for more details.
    function browser_info()
    {
        $info    = get_browser(null, true);
        $browser = $info['browser'] . ' ' . $info['version'];
        $os      = $info['platform'];
        $ip      = $_SERVER['REMOTE_ADDR'];
        return array('ip' => $ip, 'browser' => $browser, 'os' => $os);
    }

    // Quick wrapper for preg_match
    function match($regex, $str, $i = 0)
    {
        if(preg_match($regex, $str, $match) == 1)
            return $match[$i];
        else
            return false;
    }

    // Sends an HTML formatted email
    function send_html_mail($to, $subject, $message, $from, $plaintext = 'To view the message, please use an HTML compatible email viewer!', $attachment=false)
    //$to['name'] = email_address; // $from['name] = email_address;
    {
        $mail = new PHPMailer(true);
        if(!is_array($to)) { $to = array($to); }

        foreach($to as $n=>$address)
        {
            if ((!is_int($n)) && (!empty($n)))
            {
                $name = $n;
            } else {
                $name = $address;
            }

            // We need to add the addresses should there be more than one
            $mail->AddAddress($address, $name);
        }

        if(!is_array($from)) { $from = array($from); }
        foreach($from as $n=>$email)
        {
            if ((!is_int($n)) && (!empty($n)))
            {
                $name = $n;
            } else {
                $name = $email;
            }
            $from = $email;
        }

        // Send the email to the admin
        $mail->SetFrom($from, $name);
        $mail->Subject = $subject;
        $mail->AltBody = $plaintext;
        $mail->MsgHTML($message);

        if (($attachment !== false) AND (!empty($attachment)))
        {
            foreach ($attachment as $attachmentName=>$attachmentFile) {
                $mail->AddAttachment($attachmentFile,$attachmentName);
            }
        }

        // Try to send the email and fail with an exception if needed
        try
        {
            $mail->Send();
            return true;
        } catch (phpmailerException $e) {

            // Error
            return $e->errorMessage();
        }
    }

    // This function will check the DB for certain group and send the email
    function send_group_email($group, $from, $name, $subject, $message)
    {
        // Lets check if the group has any email addresses?
        $users = Auth::groupMail($group);
        if(($users)
        && (count($users) > 0)){

            $mail = new PHPMailer(true);
            foreach($users as $user){
                $mail->AddAddress($user, ucfirst($group));
            }

            // Send the email to the admin
            $mail->SetFrom($from, $name);
            $mail->Subject = $subject;
            $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
            $mail->MsgHTML($message);
            // Try to send the email and fail with an exception if needed
            try {
                $mail->Send();
                return true;

            } catch (phpmailerException $e) {

                // Error
                return $e->errorMessage();
            }

        } else {
            return false;
        }
    }

    // This function will check the DB for certain group and send the email
    function send_tier_email($group, $from, $name, $subject, $message)
    {
        // Lets check if the group has any email addresses?
        $users = Auth::tierMail($group);
        if(($users)
        && (count($users) > 0)){

            $mail = new PHPMailer(true);
            foreach($users as $user){
                $mail->AddAddress($user, ucfirst($group));
            }

            // Send the email to the admin
            $mail->SetFrom($from, $name);
            $mail->Subject = $subject;
            $mail->AltBody = 'To view the message, please use an HTML compatible email viewer!';
            $mail->MsgHTML($message);
            // Try to send the email and fail with an exception if needed
            try {
                $mail->Send();
                return true;

            } catch (phpmailerException $e) {

                // Error
                return $e->errorMessage();
            }

        } else {
            return false;
        }
    }

    // Returns the lat, long of an address via Yahoo!'s geocoding service.
    // You'll need an App ID, which is available from here:
    // http://developer.yahoo.com/maps/rest/V1/geocode.html
    // Note: needs to be updated to use PlaceFinder instead.
    function geocode($location, $appid)
    {
        $location = urlencode($location);
        $appid    = urlencode($appid);
        $data     = file_get_contents("http://local.yahooapis.com/MapsService/V1/geocode?output=php&appid=$appid&location=$location");
        $data     = unserialize($data);

        if($data === false) return false;

        $data = $data['ResultSet']['Result'];

        return array('lat' => $data['Latitude'], 'lng' => $data['Longitude']);
    }

    // A stub for Yahoo!'s reverse geocoding service
    // http://developer.yahoo.com/geo/placefinder/
    function reverse_geocode($lat, $lng)
    {

    }

    function nl($times=1)
    {
        $out = '';
        for ($i=1; $i<= $times; $i++)
        {
          $out .= "\n";
        }
        return $out;
    }

    // We are adding some mime images for documents we are allowed to upload
    function getMimeImg($mime)
    {
        $found = false;
        $dir = 'assets/img/mimes';
        if ($handle = opendir($dir))
        {
            while (false !== ($entry = readdir($handle)))
            {
                if ($entry != "." && $entry != "..")
                {
                    // We need to trim the file to get the extention from the name
                    $trimEntry = substr($entry, 0, strpos($entry, '.'));
                    //echo $mime . " " . $trimEntry . "<br />";
                    if ($mime == $trimEntry)
                    {
                        $found = $dir . "/" . $entry;
                    }
                }
            }
            closedir($handle);
        }
        if ($found)
        {
            return $found;
        } else {
            return $dir . "/unknown.png";
        }
    }

    // Get the mime type from the file extension
    function getMimeType($filename)
    {
        $getMime = substr($filename, strrpos($filename, '.')+1);
        return $getMime;
    }

    function tab($times=1)
    {
        $out = '';
        for ($i=1; $i<= $times; $i++)
        {
          $out .= "\t";
        }
        return $out;
    }

    // Quick and dirty wrapper for curl scraping.
    function curl($url, $referer = null, $post = null)
    {
        static $tmpfile;

        if(!isset($tmpfile) || ($tmpfile == '')) $tmpfile = tempnam('/tmp', 'FOO');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $tmpfile);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $tmpfile);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; U; Intel Mac OS X; en-US; rv:1.8.1) Gecko/20061024 BonEcho/2.0");
        // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($ch, CURLOPT_VERBOSE, 1);

        if($referer) curl_setopt($ch, CURLOPT_REFERER, $referer);
        if(!is_null($post))
        {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        }

        $html = curl_exec($ch);

        // $last_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        return $html;
    }

    // Accepts any number of arguments and returns the first non-empty one
    function pick()
    {
        foreach(func_get_args() as $arg)
            if(!empty($arg))
                return $arg;
        return '';
    }

    // Secure a PHP script using basic HTTP authentication
    function http_auth($un, $pw, $realm = "Secured Area")
    {
        if(!(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW']) && $_SERVER['PHP_AUTH_USER'] == $un && $_SERVER['PHP_AUTH_PW'] == $pw))
        {
            header('WWW-Authenticate: Basic realm="' . $realm . '"');
            header('Status: 401 Unauthorized');
            exit();
        }
    }

    // This is easier than typing 'echo WEB_ROOT'
    function WEBROOT()
    {
        echo WEB_ROOT;
    }

    // Class Autloader
    spl_autoload_register('framework_autoload');

    function framework_autoload($class_name)
    {
        $filename = DOC_ROOT . '/includes/classes/class.' . strtolower($class_name) . '.php';
        if(file_exists($filename))
            require $filename;

        //$filename = DOC_ROOT . '/includes/class.' . strtolower($class_name) . '.php';

    }

    // Returns a file's mimetype based on its extension
    function mime_type($filename, $default = 'application/octet-stream')
    {
        $mime_types = array('323'     => 'text/h323',
                            'acx'     => 'application/internet-property-stream',
                            'ai'      => 'application/postscript',
                            'aif'     => 'audio/x-aiff',
                            'aifc'    => 'audio/x-aiff',
                            'aiff'    => 'audio/x-aiff',
                            'asf'     => 'video/x-ms-asf',
                            'asr'     => 'video/x-ms-asf',
                            'asx'     => 'video/x-ms-asf',
                            'au'      => 'audio/basic',
                            'avi'     => 'video/x-msvideo',
                            'axs'     => 'application/olescript',
                            'bas'     => 'text/plain',
                            'bcpio'   => 'application/x-bcpio',
                            'bin'     => 'application/octet-stream',
                            'bmp'     => 'image/bmp',
                            'c'       => 'text/plain',
                            'cat'     => 'application/vnd.ms-pkiseccat',
                            'cdf'     => 'application/x-cdf',
                            'cer'     => 'application/x-x509-ca-cert',
                            'class'   => 'application/octet-stream',
                            'clp'     => 'application/x-msclip',
                            'cmx'     => 'image/x-cmx',
                            'cod'     => 'image/cis-cod',
                            'cpio'    => 'application/x-cpio',
                            'crd'     => 'application/x-mscardfile',
                            'crl'     => 'application/pkix-crl',
                            'crt'     => 'application/x-x509-ca-cert',
                            'csh'     => 'application/x-csh',
                            'css'     => 'text/css',
                            'csv2'    => 'text/x-csv',
                            'csv'     => 'text/csv',
                            'dcr'     => 'application/x-director',
                            'der'     => 'application/x-x509-ca-cert',
                            'dir'     => 'application/x-director',
                            'dll'     => 'application/x-msdownload',
                            'dms'     => 'application/octet-stream',
                            'doc'     => 'application/msword',
                            'dot'     => 'application/msword',
                            'dvi'     => 'application/x-dvi',
                            'dxr'     => 'application/x-director',
                            'eps'     => 'application/postscript',
                            'etx'     => 'text/x-setext',
                            'evy'     => 'application/envoy',
                            'exe'     => 'application/octet-stream',
                            'fif'     => 'application/fractals',
                            'flac'    => 'audio/flac',
                            'flr'     => 'x-world/x-vrml',
                            'gif'     => 'image/gif',
                            'gtar'    => 'application/x-gtar',
                            'gz'      => 'application/x-gzip',
                            'h'       => 'text/plain',
                            'hdf'     => 'application/x-hdf',
                            'hlp'     => 'application/winhlp',
                            'hqx'     => 'application/mac-binhex40',
                            'hta'     => 'application/hta',
                            'htc'     => 'text/x-component',
                            'htm'     => 'text/html',
                            'html'    => 'text/html',
                            'htt'     => 'text/webviewhtml',
                            'ico'     => 'image/x-icon',
                            'ief'     => 'image/ief',
                            'iii'     => 'application/x-iphone',
                            'ins'     => 'application/x-internet-signup',
                            'isp'     => 'application/x-internet-signup',
                            'jfif'    => 'image/pipeg',
                            'jpe'     => 'image/jpeg',
                            'jpeg'    => 'image/jpeg',
                            'jpg'     => 'image/jpeg',
                            'js'      => 'application/x-javascript',
                            'latex'   => 'application/x-latex',
                            'lha'     => 'application/octet-stream',
                            'lsf'     => 'video/x-la-asf',
                            'lsx'     => 'video/x-la-asf',
                            'lzh'     => 'application/octet-stream',
                            'm13'     => 'application/x-msmediaview',
                            'm14'     => 'application/x-msmediaview',
                            'm3u'     => 'audio/x-mpegurl',
                            'man'     => 'application/x-troff-man',
                            'mdb'     => 'application/x-msaccess',
                            'me'      => 'application/x-troff-me',
                            'mht'     => 'message/rfc822',
                            'mhtml'   => 'message/rfc822',
                            'mid'     => 'audio/mid',
                            'mny'     => 'application/x-msmoney',
                            'mov'     => 'video/quicktime',
                            'movie'   => 'video/x-sgi-movie',
                            'mp2'     => 'video/mpeg',
                            'mp3'     => 'audio/mpeg',
                            'mpa'     => 'video/mpeg',
                            'mpe'     => 'video/mpeg',
                            'mpeg'    => 'video/mpeg',
                            'mpg'     => 'video/mpeg',
                            'mpp'     => 'application/vnd.ms-project',
                            'mpv2'    => 'video/mpeg',
                            'ms'      => 'application/x-troff-ms',
                            'mvb'     => 'application/x-msmediaview',
                            'nws'     => 'message/rfc822',
                            'oda'     => 'application/oda',
                            'oga'     => 'audio/ogg',
                            'ogg'     => 'audio/ogg',
                            'ogv'     => 'video/ogg',
                            'ogx'     => 'application/ogg',
                            'p10'     => 'application/pkcs10',
                            'p12'     => 'application/x-pkcs12',
                            'p7b'     => 'application/x-pkcs7-certificates',
                            'p7c'     => 'application/x-pkcs7-mime',
                            'p7m'     => 'application/x-pkcs7-mime',
                            'p7r'     => 'application/x-pkcs7-certreqresp',
                            'p7s'     => 'application/x-pkcs7-signature',
                            'pbm'     => 'image/x-portable-bitmap',
                            'pdf'     => 'application/pdf',
                            'pfx'     => 'application/x-pkcs12',
                            'pgm'     => 'image/x-portable-graymap',
                            'pko'     => 'application/ynd.ms-pkipko',
                            'pma'     => 'application/x-perfmon',
                            'pmc'     => 'application/x-perfmon',
                            'pml'     => 'application/x-perfmon',
                            'pmr'     => 'application/x-perfmon',
                            'pmw'     => 'application/x-perfmon',
                            'pnm'     => 'image/x-portable-anymap',
                            'pot'     => 'application/vnd.ms-powerpoint',
                            'ppm'     => 'image/x-portable-pixmap',
                            'pps'     => 'application/vnd.ms-powerpoint',
                            'ppt'     => 'application/vnd.ms-powerpoint',
                            'prf'     => 'application/pics-rules',
                            'ps'      => 'application/postscript',
                            'pub'     => 'application/x-mspublisher',
                            'qt'      => 'video/quicktime',
                            'ra'      => 'audio/x-pn-realaudio',
                            'ram'     => 'audio/x-pn-realaudio',
                            'ras'     => 'image/x-cmu-raster',
                            'rgb'     => 'image/x-rgb',
                            'rmi'     => 'audio/mid',
                            'roff'    => 'application/x-troff',
                            'rtf'     => 'application/rtf',
                            'rtx'     => 'text/richtext',
                            'scd'     => 'application/x-msschedule',
                            'sct'     => 'text/scriptlet',
                            'setpay'  => 'application/set-payment-initiation',
                            'setreg'  => 'application/set-registration-initiation',
                            'sh'      => 'application/x-sh',
                            'shar'    => 'application/x-shar',
                            'sit'     => 'application/x-stuffit',
                            'snd'     => 'audio/basic',
                            'spc'     => 'application/x-pkcs7-certificates',
                            'spl'     => 'application/futuresplash',
                            'src'     => 'application/x-wais-source',
                            'sst'     => 'application/vnd.ms-pkicertstore',
                            'stl'     => 'application/vnd.ms-pkistl',
                            'stm'     => 'text/html',
                            'svg'     => "image/svg+xml",
                            'sv4cpio' => 'application/x-sv4cpio',
                            'sv4crc'  => 'application/x-sv4crc',
                            't'       => 'application/x-troff',
                            'tar'     => 'application/x-tar',
                            'tcl'     => 'application/x-tcl',
                            'tex'     => 'application/x-tex',
                            'texi'    => 'application/x-texinfo',
                            'texinfo' => 'application/x-texinfo',
                            'tgz'     => 'application/x-compressed',
                            'tif'     => 'image/tiff',
                            'tiff'    => 'image/tiff',
                            'tr'      => 'application/x-troff',
                            'trm'     => 'application/x-msterminal',
                            'tsv'     => 'text/tab-separated-values',
                            'txt'     => 'text/plain',
                            'uls'     => 'text/iuls',
                            'ustar'   => 'application/x-ustar',
                            'vcf'     => 'text/x-vcard',
                            'vrml'    => 'x-world/x-vrml',
                            'wav'     => 'audio/x-wav',
                            'wcm'     => 'application/vnd.ms-works',
                            'wdb'     => 'application/vnd.ms-works',
                            'wks'     => 'application/vnd.ms-works',
                            'wmf'     => 'application/x-msmetafile',
                            'wps'     => 'application/vnd.ms-works',
                            'wri'     => 'application/x-mswrite',
                            'wrl'     => 'x-world/x-vrml',
                            'wrz'     => 'x-world/x-vrml',
                            'xaf'     => 'x-world/x-vrml',
                            'xbm'     => 'image/x-xbitmap',
                            'xla'     => 'application/vnd.ms-excel',
                            'xlc'     => 'application/vnd.ms-excel',
                            'xlm'     => 'application/vnd.ms-excel',
                            'xls'     => 'application/vnd.ms-excel',
                            'xlt'     => 'application/vnd.ms-excel',
                            'xlw'     => 'application/vnd.ms-excel',
                            'xof'     => 'x-world/x-vrml',
                            'xpm'     => 'image/x-xpixmap',
                            'xwd'     => 'image/x-xwindowdump',
                            'z'       => 'application/x-compress',
                            'zip'     => 'application/zip');
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        return isset($mime_types[$ext]) ? $mime_types[$ext] : $default;
    }

    function microtime_float()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

      // This function is used to determine the camera details for a specific image.
    function cameraUsed($imagePath) {

        // Check if the variable is set and if the file itself exists before continuing
        if ((isset($imagePath)) and (file_exists($imagePath))) {

            $exif_ifd0 = @read_exif_data($imagePath ,'IFD0' ,0);
            $exif_exif = @read_exif_data($imagePath ,'EXIF' ,0);

            #error control
            $notFound = "''";

            // Make
            if (@array_key_exists('Make', $exif_ifd0)) {
              $camMake = $exif_ifd0['Make'];
            } else { $camMake = $notFound; }

            // Model
            if (@array_key_exists('Model', $exif_ifd0)) {
              $camModel = $exif_ifd0['Model'];
            } else { $camModel = $notFound; }

            // Exposure
            if (@array_key_exists('ExposureTime', $exif_ifd0)) {
              $camExposure = $exif_ifd0['ExposureTime'];
            } else { $camExposure = $notFound; }

            // Aperture
            if (@array_key_exists('ApertureFNumber', $exif_ifd0['COMPUTED'])) {
              $camAperture = $exif_ifd0['COMPUTED']['ApertureFNumber'];
            } else { $camAperture = $notFound; }

            // Date
            if (@array_key_exists('DateTime', $exif_ifd0)) {
              $camDate = $exif_ifd0['DateTime'];
            } else { $camDate = $notFound; }

            // ISO
            if (@array_key_exists('ISOSpeedRatings',$exif_exif)) {
              $camIso = $exif_exif['ISOSpeedRatings'];
            } else { $camIso = $notFound; }

            $return = array();
            $return['make'] = $camMake;
            $return['model'] = $camModel;
            $return['exposure'] = $camExposure;
            $return['aperture'] = $camAperture;
            $return['date'] = $camDate;
            $return['iso'] = $camIso;
            $return['image'] = $imagePath;
            return $return;

          } else {
            return false;
          }
    }

    // This is a mini addaptation of str_pad to pad a number, the default amount is 2
    function num_pad($in, $amount=2)
    {
        return str_pad($in,$amount,'0',STR_PAD_LEFT);
    }

    // Irritates me that you have to check before creating folders, this is my mini solution
    function mkdir_if_not_exist($dir)
    {
        if (!file_exists($dir))
        {
            if (mkdir($dir)) return true;
        }
        return false;
    }

    // We need to prepare the url
    function prepare_url($hostURL, $parameters, $num_pad=true)
    {
        # Ensure that we received an array with parameters
        if (is_array($parameters))
        {
            # Loop through them and replace the key value pair
            foreach($parameters as $key=>$val)
            {
                # Numpad should it be a number
                $val = ((is_numeric($val)) and $num_pad) ? num_pad($val) : $val;

                # Remove HTML Entities from the string
                $val = preg_replace("/&#?[a-z0-9]+;/i","", $val);

                # Replace the placeholders with the content we received
                $hostURL = str_replace('{' . $key . '}', normalize($val), $hostURL);
            }
        }
        return $hostURL;
    }


    // These are nice litle functions I remember from AutoIT, very usefull
    function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    // These are nice litle functions I remember from AutoIT, very usefull
    function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return (substr($haystack, -$length) === $needle);
    }

    // This function will get the nt last occurance of a string
    function strripos_nt($haystack, $needle, $nt, $trim=true)
    {
        $out = $haystack;
        for($i=1; $i<=$nt; $i++)
        {
            $lastSlash = strripos($out, $needle);
            $out = substr($out, 0, $lastSlash);
        }
        return $lastSlash;
    }

    // Create a Span with rating stars, the options are class and whether the icon should be white
    // Icon can be left blank should you not want it to be white.
    function stars($many, $class="label label-success", $icon="glyphicon glyphicon-white")
    {
    	$out = '<span class="' . $class . '">';
    	foreach(range(1, 5) as  $i)
    	{
            if ($i <= $many)
            {
                $out .= "<i class='" . $icon . " glyphicon glyphicon-star'></i>";
            } else {
                $out .= "<i class='" . $icon . " glyphicon glyphicon-star-empty'></i>";
            }
    	}
    	$out .= "</span>";
    	return $out;
    }

    // This function will check if the local file exists, and if not pull it from the server.
    function pull_image($url, $basedir, $slashes=2)
    {
        $basedir = (endsWith($basedir, '/')) ? substr($basedir, 0, -1) : $basedir;
        $firstSlash = strripos_nt($url, '/', $slashes) + 1;
        $lastSlash = strripos($url, '/') + 1;
        $fileName = substr($url, $lastSlash);
        $filePath = str_replace('/' . $fileName, '', substr($url, $firstSlash));
        $localFile = implode('/', array($basedir, $filePath, $fileName));
        $extension = pathinfo($localFile);
        $createDir =  implode('/', array($basedir, $filePath));
        mkdir_if_not_exist($createDir);
        ///echo $localFile . " & " . $url;
        if ((!file_exists($localFile))
        or (filesize($localFile) < 1024))
        {
            # Store the image
            @file_put_contents($localFile, @file_get_contents($url));

            # Ensure that it's not empty
            if (!empty($extension["extension"]))
            {
                # Fire up GD and resample the image
                $GD = new GD($localFile);
                $GD->saveAs($localFile, strtolower($extension["extension"]), 75);
            }
        }
        return $localFile;
    }

    // This sort of extends the pull image function
    function pull_image_exists($url, $basedir, $slashes=2)
    {
        $basedir = (endsWith($basedir, '/')) ? substr($basedir, 0, -1) : $basedir;
        $firstSlash = strripos_nt($url, '/', $slashes) + 1;
        $lastSlash = strripos($url, '/') + 1;
        $fileName = substr($url, $lastSlash);
        $filePath = str_replace('/' . $fileName, '', substr($url, $firstSlash));
        $localFile = implode('/', array($basedir, $filePath, $fileName));
        return file_exists($localFile);
    }

    // This will be used to normallize a string to an extent, to be used in accents
    function normalize($string)
    {
        $terms = array(
            "&"     => "and",
            "'"     => "",
            "Live"  => "",
            "."     => "",
            "("     => "",
            ")"     => ""
        );

        foreach($terms as $find => $replace)
        {
            $string = str_replace($find, $replace, $string);
        }
        return $string;
    }

    // Create a SalesForce Link
    function linkSF($caseNumber)
    {
        $sfLink = "https://na6.salesforce.com/search/SearchResults?searchType=2&asPhrase=1&sen=500&str=" . $caseNumber;
        return $sfLink;
    }

    // Create a Mantis link
    function linkMantis($mantisNumber)
    {
        $mantisLink = "http://mantis.internal.clickatell.com/view.php?id=" . $mantisNumber;
        return $mantisLink;
    }

    // Get the difference between a timestamp and now
    function dateDif($timestamp, $mdy='d') // currentl this works for days options include M=month, m=minutes, h=hours, s=seconds
    {
        $now = time(); // or your date as well
        $datediff = $now - $timestamp;
        $return = false;
        switch ($mdy)
        {
            case 'd':
                $return = floor($datediff/(60*60*24));
                continue;

            case 'm':
                $return = floor($datediff/(60*60));
                continue;

            case 'M':
                $return = floor($datediff/(60*60*24*12));
                continue;

            case 's':
                $return = floor($datediff);
                continue;

            default:
                $return = floor($datediff/(60*60*24));
                continue;
        }
        return  $return;
    }

    // This will convert the date and time picker values to a timestamp
    function dataTimePicker($datepicker, $timepicker=false)
    {
        $split = explode('/', $datepicker);
        $d = $split[0];
        $m = $split[1];
        $y = $split[2];

        // Check if the timepicker was passed in or not, otherwise set a default.
        if ($timepicker !== false)
        {
            $split = explode(":", $timepicker);
            $h = $split[0];
            $min = $split[1];
        } else {
            $h = 0;
            $min = 0;
        }

        $timestamp = mktime($h, $min, 0, $m, $d, $y);
        return $timestamp;
    }


    // Lets build a function to quickly get the username of the user for our domain which is a name.surname username
    function dotName($in)
    {
        // Check if there is a dot.
        if (strstr($in, '.')) {
            $in = substr($in, 0, strpos($in, '.'));
        }

        // Then return the name with a uppercase character
        return ucfirst($in);
    }

    // This function will count the amonut of array keys which contain a valid value
    function count_array($array)
    {
        $count = 0;
        foreach($array as $item)
        {
            if ($item !== '')
            {
                $count += 1;
            }
        }
        return $count;
    }

    # This is a little hack job to make the badges prettier
    function badge($string, $badge_type, $title="")
    {
        $out = '<span title="' . $title . '" class="badge badge-' . $badge_type . '">' . $string . '</span>';
        return $out;
    }


    # This is a little hack job to make the badges prettier
    function btn($string, $button_type=false, $title="")
    {
        $addBtn = ($button_type) ? ' btn-'. $button_type : "";
        $out = '<span title="' . $title . '" class="btn btn-default ' . $addBtn . '">' . $string . '</span>';
        return $out;
    }


    # This is a little hack job to make the bootstrap labels prettier
    function label($string, $label_type, $title="")
    {
        $out = '<span title="' . $title . '" class="label label-' . $label_type . '">' . $string . '</span>';
        return $out;
    }


    # This is a little hack job to make the bootstrap icons prettier
    function icon($icon_type, $white=false, $title="")
    {
        $add = ($white) ? " glyphicon glyphicon-white" : "";
        $out = '<i title="' . $title . '" class="glyphicon glyphicon-' . $icon_type.$add. '"></i>';
        return $out;
    }


    # Let's create some links quickly and easily
    function href($url, $name, $btn=false, $title="")
    {
        $add = ($btn) ? ' class="btn btn-default btn-' . $btn . ' "' : "";
        $out = '<a href="' . $url . '" title="' . $title . '" ' . $add . '>' . $name . '</a>';
        return $out;
    }

    /*function cleanse($string)
    {
        $string = str_replace('–', '-', $string);
        $string = @iconv("UTF-8", "ISO-8859-1//IGNORE", $string);
        $string = @iconv("ISO-8859-1", "UTF-8", $string);

        $string = htmlentities($string);
        $string = str_replace("'", "&apos;", $string);

        return $string;
    }*/

    function cleanse($string)
    {
      // Replace Single Curly Quotes
      $search[]  = chr(226).chr(128).chr(152);
      $replace[] = "'";
      $search[]  = chr(226).chr(128).chr(153);
      $replace[] = "'";

      // Replace Smart Double Curly Quotes
      $search[]  = chr(226).chr(128).chr(156);
      $replace[] = '"';
      $search[]  = chr(226).chr(128).chr(157);
      $replace[] = '"';

      // Replace En Dash
      $search[]  = chr(226).chr(128).chr(147);
      $replace[] = '--';

      // Replace Em Dash
      $search[]  = chr(226).chr(128).chr(148);
      $replace[] = '---';

      // Replace Bullet
      $search[]  = chr(226).chr(128).chr(162);
      $replace[] = '*';

      // Replace Middle Dot
      $search[]  = chr(194).chr(183);
      $replace[] = '*';

      // Replace Ellipsis with three consecutive dots
      $search[]  = chr(226).chr(128).chr(166);
      $replace[] = '...';

      // Apply Replacements
      $string = str_replace($search, $replace, $string);

      // Remove any non-ASCII Characters
      $string = preg_replace("/[^\x01-\x7F]/","", $string);
      $string = htmlentities($string);
      $string = str_replace("'", "&apos;", $string);
      $string = mysql_real_escape_string($string);
      return $string;
    }