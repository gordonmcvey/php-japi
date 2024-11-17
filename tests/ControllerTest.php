<?php

use Docnet\JAPI\Http\Enum\Verbs;
use Docnet\JAPI\Http\Request;
use Docnet\JAPI\Http\RequestInterface;
use PHPUnit\Framework\TestCase;

require_once('Controllers/Example.php');
require_once('Controllers/Headers.php');
require_once('Controllers/Exceptional.php');
require_once('Controllers/JsonParams.php');
require_once('Controllers/ProtectedFunctions.php');

class ControllerTest extends TestCase
{

    public function testBasicResponse()
    {
        $obj_controller = new Example($this->createMock(RequestInterface::class));
        $obj_controller->dispatch();
        $this->assertEquals($obj_controller->getResponse(), ['test' => TRUE]);
    }

    public function testQuery()
    {
        // $_GET['input1'] = 'value1';
        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->once())->method("queryParam")->with("input1")->willReturn("value1");

        $obj_controller = new \Hello\World($request);
        $obj_controller->dispatch();
        $obj_response = $obj_controller->getResponse();
        $this->assertEquals($obj_response['input1'], 'value1');
    }

    public function testPost()
    {
        // $_POST['input2'] = 'value2';
        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->once())->method("postParam")->with("input2")->willReturn("value2");

        $obj_controller = new \Hello\World($request);
        $obj_controller->dispatch();
        $obj_response = $obj_controller->getResponse();
        $this->assertEquals($obj_response['input2'], 'value2');
    }

    public function testParam()
    {
        // $_GET['input3'] = 'value3';
        // $_POST['input4'] = 'value4';

        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->exactly(2))->method("param")->willReturnMap([
            ["input3", null, "value3"],
            ["input4", null,  "value4"],
        ]);

        $obj_controller = new \Hello\World($request);
        $obj_controller->dispatch();
        $obj_response = $obj_controller->getResponse();

        $this->assertEquals("value3", $obj_response['input3']);
        $this->assertEquals("value4", $obj_response['input4']);
    }

    public function testMixedParam()
    {
        $this->markTestSkipped("No longer relevant with the Request class");
        // $_GET['input4'] = 'value4-get';
        // $_POST['input4'] = 'value4-post';
        // $obj_controller = new \Hello\World($this->createMock(RequestInterface::class));
        // $obj_controller->dispatch();
        // $obj_response = $obj_controller->getResponse();

        // print_r($obj_response);

        // $this->assertEquals('value4-get', $obj_response['input4']);
    }

    public function testCliHeaders()
    {
        // $_SERVER['HTTP_SOME_HEADER'] = TRUE;
        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->once())->method('headers')->willReturn(['Some-Header' => true]);

        $obj_controller = new Headers($request);
        $obj_controller->dispatch();
        $this->assertEquals($obj_controller->getResponse(), ['Some-Header' => TRUE]);
    }

    public function testJsonBodyParam()
    {
        $str_json = '{"json_param": "param_found"}';
        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->once())->method('body')->willReturn($str_json);

        $obj_controller = new \JsonParams($request);
        $obj_controller->setBody($str_json);
        $obj_controller->dispatch();
        $obj_response = $obj_controller->getResponse();
        $this->assertEquals('param_found', $obj_response['json_param']);
        $this->assertEquals('default_value', $obj_response['missing_param']);
    }

    public function testIsPost() {
        // $_SERVER['REQUEST_METHOD'] = 'POST';
        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->once())->method('verb')->willReturn(Verbs::POST);

        $obj_controller = new ProtectedFunctions($request);
        $this->assertTrue($obj_controller->getIsPost());
    }
}
