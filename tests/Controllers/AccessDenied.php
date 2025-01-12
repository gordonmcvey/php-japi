<?php

use Docnet\JAPI\Controller\Controller;

class AccessDenied extends Controller
{
    public function dispatch(){
        throw new \Docnet\JAPI\Exceptions\AccessDenied('Error Message', 403);
    }
}