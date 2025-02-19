<?php

use gordonmcvey\httpsupport\JsonRequestInterface;
use gordonmcvey\httpsupport\RequestInterface;
use PHPUnit\Framework\TestCase;

class ControllerTest extends TestCase
{

    public function testBasicResponse()
    {
        $request = $this->createMock(RequestInterface::class);
        $obj_controller = new Example($request);
        $response = $obj_controller->dispatch($request);

        $this->assertEquals((object) ['test' => true], json_decode($response->body()));
    }

    public function testQuery()
    {
        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->once())->method("queryParam")->with("input1")->willReturn("value1");

        $obj_controller = new \Hello\World($request);
        $obj_response = json_decode($obj_controller->dispatch($request)->body());

        $this->assertEquals('value1', $obj_response->input1);
    }

    public function testPost()
    {
        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->once())->method("postParam")->with("input2")->willReturn("value2");

        $obj_controller = new \Hello\World($request);
        $obj_response = json_decode($obj_controller->dispatch($request)->body());

        $this->assertEquals('value2', $obj_response->input2);
    }

    public function testParam()
    {
        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->exactly(2))->method("param")->willReturnMap([
            ["input3", null, "value3"],
            ["input4", null,  "value4"],
        ]);

        $obj_controller = new \Hello\World($request);
        $obj_response = json_decode(json: $obj_controller->dispatch($request)->body());

        $this->assertEquals("value3", $obj_response->input3);
        $this->assertEquals("value4", $obj_response->input4);
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
        $request = $this->createMock(RequestInterface::class);
        $request->expects($this->once())->method('headers')->willReturn(['Some-Header' => true]);

        $obj_controller = new Headers($request);
        $obj_response = json_decode(json: $obj_controller->dispatch($request)->body());

        $this->assertEquals(true, $obj_response->{'Some-Header'});
    }

    public function testJsonBodyParam()
    {
        $request = $this->createMock(JsonRequestInterface::class);
        $request->expects($this->exactly(2))
            ->method("param")
            ->willReturnMap([
                ["json_param", "default_value", "param_found"],
                ["missing_param", "default_value", "default_value"],
            ]);

        $obj_controller = new \JsonParams($request);
        $obj_response = json_decode($obj_controller->dispatch($request)->body());

        $this->assertEquals('param_found', $obj_response->json_param);
        $this->assertEquals('default_value', $obj_response->missing_param);
    }

    // public function testIsPost() {
    //     // $_SERVER['REQUEST_METHOD'] = 'POST';
    //     $request = $this->createMock(RequestInterface::class);
    //     $request->expects($this->once())->method('verb')->willReturn(Verbs::POST);

    //     $obj_controller = new ProtectedFunctions($request);
    //     $this->assertTrue($obj_controller->getIsPost());
    // }
}
