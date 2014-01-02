<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL | E_STRICT);

include("includes/master.inc.php");

function human_filesize($bytes, $decimals = 2)
{
  $sz = 'BKMGTP';
  $factor = floor((strlen($bytes) - 1) / 3);
  return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor] . "B";
}

function getFileInfo($file)
{
    echo "<b>FILE SIZE: </b>" . human_filesize(filesize($file)) . "<br />";
}

# NORMAL TEST
$MEM = new MemUsage();
$TIMER1 = new StopWatch();

$JS = new Merge('./assets/js/');
$JS->add("cookies.js");
$JS->add("jquery.js");
$JS->add("bootstrap.min.js");
$JS->add("bootstrap.file-input.js");
$JS->add("custom.js");
$JS->output("export.js");
$JS->export();

echo "REGULAR<br />";
echo $MEM->used();
echo $TIMER1->display();
echo getFileInfo("assets/js/out/export.js");
echo '<a href="assets/js/out/export.js" target="_BLANK">OPEN</a><br />';


# NEW FUNCTIOnTEST
$MEM2 = new MemUsage();

$JS = new Merge('./assets/js/');
$JS->add("cookies.js");
$JS->add("jquery.js");
$JS->add("bootstrap.min.js");
$JS->add("bootstrap.file-input.js");
$JS->add("custom.js");
$JS->output("export2.js");
$JS->exportTest();

echo "<br /><br />TEST<br />";
echo $MEM2->used();
echo $TIMER1->display();
echo getFileInfo("assets/js/out/export2.js");
echo '<a href="assets/js/out/export2.js" target="_BLANK">OPEN</a><br />';