<?php

use Docnet\JAPI;
use Docnet\JAPI\Http\Enum\SuccessCodes;
use Docnet\JAPI\Http\RequestInterface;
use Docnet\JAPI\Http\Response;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

require_once('Controllers/Example.php');
require_once('Controllers/Exceptional.php');
require_once('Controllers/Whoops.php');
require_once('Controllers/AccessDenied.php');

class JAPITest extends TestCase
{

    /**
     * Test the dispatch() cycle
     */
    public function testDispatchCycle()
    {
        // Mocked controller & expectations
        $obj_controller = $this->getMockBuilder(Example::class)->disableOriginalConstructor()->getMock();
        
        $obj_controller->expects($this->once())->method('preDispatch');
        $obj_controller->expects($this->once())->method('dispatch');
        $obj_controller->expects($this->once())->method('postDispatch');

        // Mock JAPI (just replace the sendResponse method to avoid output errors)
        $obj_japi = $this->getMockBuilder(JAPI::class)->onlyMethods(['sendResponse'])->getMock();

        // Dispatch
        $obj_japi->dispatch($obj_controller);
    }

    /**
     * Validate the bootstrap() cycle works on a supplied Controller
     */
    public function testConcreteBootstrapCycle()
    {
        // Mocked controller & expectations
        $obj_controller = $this->getMockBuilder(Example::class)->disableOriginalConstructor()->getMock();

        $obj_controller->expects($this->once())->method('preDispatch');
        $obj_controller->expects($this->once())->method('dispatch');
        $obj_controller->expects($this->once())->method('postDispatch');

        // Mock JAPI (just replace the sendResponse method to avoid output errors)
        $obj_japi = $this->getMockBuilder(JAPI::class)->onlyMethods(['sendResponse'])->getMock();
        $obj_japi->expects($this->once())->method('sendResponse');

        // Dispatch
        $obj_japi->bootstrap($obj_controller);
    }

    /**
     * Test the bootstrap() methods correctly executes the supplied callback
     *
     * @todo Implement this test!
     */
    public function testBootstrapCallback()
    {
        $this->markTestIncomplete("Test for BootstrapCallback hasn't been implemented yet");
    }

    /**
     * Test Exceptions are correctly passed to jsonError from the bootstrap() method
     */
    public function testBootstrapErrorCycle()
    {
        // Mock JAPI
        $obj_japi = $this->getMockBuilder(JAPI::class)->onlyMethods(['sendResponse', 'jsonError'])->getMock();
        $obj_japi->expects($this->never())->method('sendResponse');
        $obj_japi->expects($this->once())->method('jsonError')->with(
            $this->equalTo(new Exception()),
        );

        // Dispatch
        $obj_japi->bootstrap(new Whoops($this->createMock(RequestInterface::class)));
    }

    /**
     * Test custom Exception codes are correctly passed to jsonError from the bootstrap() method
     */
    public function testBootstrapCustomErrorCycle()
    {
        // Mock JAPI
        $obj_japi = $this->getMockBuilder(JAPI::class)->onlyMethods(['sendResponse', 'jsonError'])->getMock();
        $obj_japi->expects($this->never())->method('sendResponse');
        $obj_japi->expects($this->once())->method('jsonError')->with(
            $this->equalTo(new RuntimeException('Error Message', 400)),
        );

        // Dispatch
        $obj_japi->bootstrap(new Exceptional($this->createMock(RequestInterface::class)));
    }

    /**
     * Validate the response data from the Controller is correctly passed to sendResponse()
     */
    public function testSendResponse()
    {
        // Mock JAPI
        $obj_japi = $this->getMockBuilder(JAPI::class)->onlyMethods(['sendResponse'])->getMock();
        $obj_japi->expects($this->once())
            ->method('sendResponse')
            ->with(
                $this->equalTo(new Response(
                    SuccessCodes::OK,
                    json_encode(['test' => true])
            )),
        );

        // Dispatch
        $obj_japi->bootstrap(new Example($this->createMock(RequestInterface::class)));
    }

    /**
     * Do we correctly call the logger in jsonError scenarios?
     */
    public function testLogger()
    {
        // Mock the logger
        $obj_logger = $this->getMockBuilder(LoggerInterface::class)->getMock();
        $obj_logger->expects($this->once())->method('error');;

        // Mock JAPI
        $obj_japi = $this->getMockBuilder(JAPI::class)->onlyMethods(['sendResponse'])->getMock();
        $obj_japi->setLogger($obj_logger);

        // Dispatch
        $obj_japi->bootstrap(new Exceptional($this->createMock(RequestInterface::class)));
    }

    /**
     * Ensure we do not fall over when no logger has been supplied
     */
    public function testNoLogger()
    {
        // Mock JAPI
        $obj_japi = $this->getMockBuilder(JAPI::class)->onlyMethods(['sendResponse'])->getMock();

        // Dispatch
        $obj_japi->bootstrap(new Exceptional($this->createMock(RequestInterface::class)));

        // If something went wrong then we'd never get here
        $this->assertTrue(true);
    }

    /**
     * Test an AccessDenied Exception codes are correctly passed to jsonError from the bootstrap() method
     */
    public function testBootstrapAccessDeniedErrorCycle()
    {
        // Mock JAPI
        $obj_japi = $this->getMockBuilder(JAPI::class)->disableOriginalConstructor()->onlyMethods(['sendResponse', 'jsonError'])->getMock();
        $obj_japi->expects($this->never())->method('sendResponse');
        $obj_japi->expects($this->once())->method('jsonError')->with(
            $this->equalTo(new \Docnet\JAPI\Exceptions\AccessDenied('Error Message', 403)),
//            $this->equalTo(403)
        );

        // Dispatch
        $obj_japi->bootstrap(new AccessDenied($this->createMock(RequestInterface::class)));
    }
}
