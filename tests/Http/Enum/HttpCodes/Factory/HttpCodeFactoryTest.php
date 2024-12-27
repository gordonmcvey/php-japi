<?php 

declare(strict_types=1);

use Docnet\JAPI\Http\Enum\HttpCodes\ClientErrorCodes;
use Docnet\JAPI\Http\Enum\HttpCodes\Factory\HttpCodeFactory;
use Docnet\JAPI\Http\Enum\HttpCodes\InfoCodes;
use Docnet\JAPI\Http\Enum\HttpCodes\RedirectCodes;
use Docnet\JAPI\Http\Enum\HttpCodes\ServerErrorCodes;
use Docnet\JAPI\Http\Enum\HttpCodes\SuccessCodes;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class HttpCodeFactoryTest extends TestCase
{
    #[Test]
    public function itInstantiatesProperEnumsForInts(): void
    {
        $factory = new HttpCodeFactory();
        $this->assertSame(InfoCodes::EARLY_HINTS, $factory->fromInt(InfoCodes::EARLY_HINTS->value));
        $this->assertSame(SuccessCodes::CREATED, $factory->fromInt(SuccessCodes::CREATED->value));
        $this->assertSame(RedirectCodes::FOUND, $factory->fromInt(RedirectCodes::FOUND->value));
        $this->assertSame(ClientErrorCodes::CONFLICT, $factory->fromInt(ClientErrorCodes::CONFLICT->value));
        $this->assertSame(ServerErrorCodes::BAD_GATEWAY, $factory->fromInt(ServerErrorCodes::BAD_GATEWAY->value));
    }

    #[Test]
    public function itInstantiatesProperEnumsForInvalidInts(): void
    {
        $factory = new HttpCodeFactory();
        $this->assertSame(ServerErrorCodes::INTERNAL_SERVER_ERROR, $factory->fromInt(99));
        $this->assertSame(ServerErrorCodes::INTERNAL_SERVER_ERROR, $factory->fromInt(199));
        $this->assertSame(ServerErrorCodes::INTERNAL_SERVER_ERROR, $factory->fromInt(299));
        $this->assertSame(ServerErrorCodes::INTERNAL_SERVER_ERROR, $factory->fromInt(399));
        $this->assertSame(ServerErrorCodes::INTERNAL_SERVER_ERROR, $factory->fromInt(499));
        $this->assertSame(ServerErrorCodes::INTERNAL_SERVER_ERROR, $factory->fromInt(599));
        $this->assertSame(ServerErrorCodes::INTERNAL_SERVER_ERROR, $factory->fromInt(699));
    }

    #[Test]
    public function itInstantiatesProperlyFromErrors(): void
    {
        $factory = new HttpCodeFactory();
        $this->assertSame(
            ClientErrorCodes::CONFLICT,
            $factory->fromThrowable(new \Exception(code: ClientErrorCodes::CONFLICT->value)),
        );
        $this->assertSame(
            ServerErrorCodes::BAD_GATEWAY,
            $factory->fromThrowable(new \Exception(code: ServerErrorCodes::BAD_GATEWAY->value)),
        );
    }

    #[Test]
    public function itInstantiatesProperlyFromErrorsWithOutOfRangeCodes(): void
    {
        $factory = new HttpCodeFactory();
        $this->assertSame(
            ServerErrorCodes::INTERNAL_SERVER_ERROR,
            $factory->fromThrowable(new \Exception(code: 0)),
        );
        $this->assertSame(
            ServerErrorCodes::INTERNAL_SERVER_ERROR,
            $factory->fromThrowable(new \Exception(code: 399)),
        );
        $this->assertSame(
            ServerErrorCodes::INTERNAL_SERVER_ERROR,
            $factory->fromThrowable(new \Exception(code: 600)),
        );
    }
}
