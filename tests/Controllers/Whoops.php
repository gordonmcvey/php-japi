<?php

use Docnet\JAPI\Controller\Controller;

class Whoops extends Controller
{
    public function dispatch(){
        throw new Exception;
    }
}