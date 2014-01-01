<?PHP
    class BBCode
    {
        public static function decode($string_in)
        {
            $search = array(
                '#\[color=(.*?)\](.*?)\[/color\]#ism',
                '#\[h1\](.*?)\[/h1\]#is',
                '#\[h2\](.*?)\[/h2\]#is',
                '#\[b\](.*?)\[/b\]#ism',
                '#\[i\](.*?)\[/i\]#ism',
                '#\[u\](.*?)\[/u\]#ism',
                '#\*\*\*(.*?)\\n#is',
                '#===(.*?)\\n#is',
                '#---(.*?)\\n#is',
                '#\[btn=(.*?)\](.*?)\[/btn\]#is',
                '#\[url=(.*?)\](.*?)\[/url\]#ism',

            );

            $replace = array(
                '<font color="\\1">\\2</font>',
                '<h3 style="color: black;">\\1</h3>',
                '<h4 style="color: black;">\\1</h4>',
                '<b>\\1</b>',
                '<i>\\1</i>',
                '<u>\\1</u>',
                '<li class="span">\\1</li><div class="clearfix"></div>',
                '<h3 style="color: black;">\\1</h3>',
                '<h4 style="color: black;">\\1</h4>',
                '<a style="margin-right: 15px;" class="btn btn-default btn-lg" target="_blank" href="\\1">\\2</a>',
                '<a target="_blank" href="\\1">\\2</a>',
            );
            return preg_replace($search, $replace, $string_in);
        }


        // Custom codify function to convert [code]*[/code] to a awesome looking highlighter
        public static function codify($string)
        {
            $new = '<pre class="prettyprint ">';
            $search = "#\[code\](.*?)\[/code\]#s";
            $reg = preg_match($search, $string, $out);

            if (count($out) > 0)
            {
                $out = explode("\n", $out[0]);
                foreach($out as $key=>$line)
                {
                    $line = str_replace('<br />', '', $line);
                    $line = str_replace('[code]', '', $line);
                    $line = str_replace('[/code]', '', $line);
                    $new .=  $line;
                }
                $new .= "</pre>";
                return preg_replace($search, $new, $string);
            }
            return $string;
        }


        // Lets do the file one
        public static function filify($string)
        {
            $search = '#\[file\](.*?)\[/file\]#ism';
            $reg = preg_match_all($search, $string, $out);

            // We need to ensure that this loop only runs when a file is actually found within the string.
            if ( (isset($out[1])) AND (count($out[1]) > 0) )
            {
                foreach($out[1] as $key=>$file)
                {
                    $new = '<div class="col-lg-1">';
                    $ext = getMimeType($file);
                    $attachment = $file;
                    $filename = substr($attachment, strrpos($attachment, '/')+1);
                    $mimeImg = getMimeImg($ext);
                    $new .= "<a class='thumbnail toolTip' target='_blank' title='Open / Save " . $filename . "' href='" . $attachment . "'><img width='64' src='" . $mimeImg . "' alt='" . $mimeImg . "' /></a>";
                    $new .= "</div>";
                    $new .= "<div class='clearfix'></div>";
                    $string = str_replace($out[0][$key], $new, $string);
                }
            }
            return $string;
        }


        // Custom Imagify function.
        public static function imagic($string)
        {
            $search = '#\[img\](.*?)\[/img\]#ism';
            $reg = preg_match_all($search, $string, $out);

            if (count($out) > 0)
            {
                $original = $out[0];
                $images = $out[1];
                foreach($images as $key=>$image)
                {
                    // Check for the large version
                    $path = "assets/uploads/";
                    $largePath = $path . "large/";

                    // Does the file exist locally?
                    if ((file_exists(str_replace($path, $largePath, $image))) OR (file_exists($image)))
                    {
                        $replace = '<div class="col-sm-6">
                                        <a target="_blank" href="' . str_replace($path, $largePath, $image) . '">
                                            <img class="img-thumbnail img-responsive" alt="Image missing ' . $image . '" src="' . $image . '" />
                                        </a>
                                    </div>
                                    <div class="clearfix"></div>';
                    }

                    else
                    {
                        $replace = '<div class="col-sm-6">
                                        <a target="_blank" href="' . $image . '">
                                            <img class="img-thumbnail img-responsive" alt="Image missing ' . $image . '" src="' . $image . '" />
                                        </a>
                                    </div>
                                    <div class="clearfix"></div>';
                    }
                    $string = str_replace($original[$key], $replace, $string);
                }
            }
            return $string;
        }


        // Custom Imagify function.
        public static function imagicPlain($string)
        {
            $search = '#\[img\](.*?)\[/img\]#ism';
            $reg = preg_match_all($search, $string, $out);

            if (count($out) > 0)
            {
                $original = $out[0];
                $images = $out[1];
                foreach($images as $key=>$image)
                {
                    // Check for the large version
                    $path = "assets/uploads/";
                    $largePath = $path . "large/";

                    // Does the file exist locally?
                    if (file_exists(str_replace($path, $largePath, $image)))
                    {
                        $replace = '<img width="50" alt="Image missing ' . $image . '" src="' . $image . '" />';
                    }
                    else
                    {
                        // No it's on someone else's server
                        $replace = '<img width="50" alt="Image missing ' . $image . '" src="' . $image . '" />';
                    }
                    $string = str_replace($original[$key], $replace, $string);
                }
            }

            return $string;
        }


        // Custom Imagify function.
        public static function imagicFB($string)
        {
            $return = array('string'=>$string, 'images'=>array());
            $search = '#\[img\](.*?)\[/img\]#ism';
            $reg = preg_match_all($search, $string, $out);

            if (count($out) > 0)
            {
                $original = $out[0];
                $images = $out[1];
                foreach($images as $key=>$image)
                {
                    $return['images'][] .= Config::get('authDomain') . WEB_ROOT . "/" .  $image;
                    $return['string'] = str_replace($original[$key], '', $return['string']);
                }
            }
            return $return;
        }


        // Similar to imagic, but to check if there is a large version locally and return a link for it
        public static function imageFullSize($image, $dir='portfolio')
        {
            // Check for the large version
            $path = "assets/" . $dir . "/";
            $largePath = $path . "large/";

            // Does the file exist locally?
            if (file_exists(str_replace($path, $largePath, $image)))
            {
                $image = str_replace($path, $largePath, $image);
            }
            return $image;
        }


        // This function will return an array of all the available BBCode.
        public static function showAll()
        {
            $bbcode = array('Large Heading'         =>array('icon'=>'glyphicon glyphicon-header',       'code'=>'[h1][/h1]',                                'pre'=>'[h1]',      'post'=>'[/h1]'),
                            'Medium Heading'        =>array('icon'=>'glyphicon glyphicon-header',       'code'=>'[h2][/h2]',                                'pre'=>'[h2]',      'post'=>'[/h2]'),
                            'Bold'                  =>array('icon'=>'glyphicon glyphicon-bold',         'code'=>'[b][/b]',                                  'pre'=>'[b]',       'post'=>'[/b]'),
                            'Italics'               =>array('icon'=>'glyphicon glyphicon-italic',       'code'=>'[i][/i]',                                  'pre'=>'[i]',       'post'=>'[/i]'),
                            'Underline'             =>array('icon'=>'glyphicon glyphicon-text-width',   'code'=>'[u][/u]',                                  'pre'=>'[u]',       'post'=>'[/u]'),
                            'Bullet Point'          =>array('icon'=>'glyphicon glyphicon-asterisk',     'code'=>'*** Bullet me baby',                       'pre'=>'***',       'post'=>''),
                            'Image'                 =>array('icon'=>'glyphicon glyphicon-picture',      'code'=>'[img][/img]',                              'pre'=>'[img]',     'post'=>'[/img]'),
                            'Link'                  =>array('icon'=>'glyphicon glyphicon-link',         'code'=>'[url=http://link.com]Link Name[/url]',     'pre'=>'[url=',     'post'=>']Link Name[/url]'),
                            'Button Link'           =>array('icon'=>'glyphicon glyphicon-bookmark',     'code'=>'[btn=http://link.com]Link Name[/btn]',     'pre'=>'[btn=',     'post'=>']Link Name[/btn]'),
                            'Code'                  =>array('icon'=>'glyphicon glyphicon-barcode',      'code'=>'[code][/code]',                            'pre'=>'[code]',    'post'=>'[/code]'),
                            'File'                  =>array('icon'=>'glyphicon glyphicon-file',         'code'=>'[file][/file]',                            'pre'=>'[file]',    'post'=>'[/file]'),
                            'Color'                 =>array('icon'=>'glyphicon glyphicon-tint',         'code'=>'[color=red][/color]',                      'pre'=>'[color=red]','post'=>'[/color]'),
                            'Horizontal Ruler'      =>array('icon'=>'glyphicon glyphicon-minus',        'code'=>'<hr />',                                   'pre'=>'',          'post'=>'<hr />'),
                            );
            return $bbcode;
        }


        // This is not really BBCode, but lets keep it here
        public static function mailify($text)
        {
            $regex = '/(\S+@\S+\.\S+)/i';
            $replace = "<a href='mailto:$1'>$1</a>";
            $result = preg_replace($regex, $replace, $text);
            return $result;
        }


        // This function will automatically create twitter links
        public static function twitter($text)
        {
            $text= preg_replace("/@(\w+)/", '<a href="http://www.twitter.com/$1" target="_blank">@$1</a>', $text);
            $text= preg_replace("/\#(\w+)/", '<a href="http://search.twitter.com/search?q=$1" target="_blank">#$1</a>',$text);
            return $text;
        }


        // Automatically create links
        public static function linkify($text)
        {
            $text= preg_replace("/(^|[\n ])([\w]*?)((ht|f)tp(s)?:\/\/[\w]+[^ \,\"\n\r\t<]*)/is", "$1$2<a target=\"_blank\" href=\"$3\" >$3</a>", $text);
            $text= preg_replace("/(^|[\n ])([\w]*?)((www|ftp)\.[^ \,\"\t\n\r<]*)/is", "$1$2<a target=\"_blank\" href=\"http://$3\" >$3</a>", $text);
            $text= preg_replace("/(^|[\n ])([a-z0-9&\-_\.]+?)@([\w\-]+\.([\w\-\.]+)+)/i", "$1<a target=\"_blank\" href=\"mailto:$2@$3\">$2@$3</a>", $text);
            return($text);
        }


        // This is a templatized bbcode version
        public static function allInOne($text)
        {
            //$text = htmlentities($text);
    	    $text = nl2br($text);
    	    $text = BBCode::imagic($text);
    	    $text = BBCode::codify($text);
    	    $text = BBCode::mailify($text);
    	    $text = BBCode::decode($text);
    	    $text = BBCode::linkify($text);
    	    $text = BBCode::filify($text);
            return $text;
        }


    }
?>