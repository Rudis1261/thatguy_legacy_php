<?php

$CSS = new Merge('./assets/css/'); // new class to merge the CSS as needed
$CSS->add("bootstrap.min.css");
$CSS->add("custom.css");

# Ensure that the responsive is loaded last
$CSS->add("responsive.custom.css");