<?php

class MemUsage {

    public $start;

    public function __construct() {
        $this->start = memory_get_usage();
    }

    # Conversion function
    public function convert($size)
    {
        $unit=array('b','kb','mb','gb','tb','pb');
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2) . strtoupper($unit[$i]);
    }

    # Display the memory used
    public function used()
    {
        $used = memory_get_usage();
        return "<b>MEMORY USED: </b>" . $this->convert($used - $this->start) . "<br />";
    }
}