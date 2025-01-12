<?php

use Docnet\JAPI\Controller\Controller;

class ProtectedFunctions extends Controller
{

    public function dispatch(){
        $this->setResponse(true);
    }


    public function getIsPost() {
        return $this->isPost();
    }
}