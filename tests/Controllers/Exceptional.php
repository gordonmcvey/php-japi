<?php

use Docnet\JAPI\Controller\Controller;

class Exceptional extends Controller
{
    public function dispatch(){
        throw new RuntimeException('Error Message', 400);
    }
}