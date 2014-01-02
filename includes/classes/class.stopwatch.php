<?php

class StopWatch {

    public $total;
    public $time;

    public function __construct()
    {
        $this->total = $this->time = microtime(true);
    }

    public function clock()
    {
        return -$this->time + ($this->time = microtime(true));
    }

    public function elapsed()
    {
        return microtime(true) - $this->total;
    }

    public function reset()
    {
        $this->total=$this->time=microtime(true);
    }

    public function display()
    {
        return "<b>EXEC TIME: </b>" . $this->clock() . " seconds<br />";
    }
}