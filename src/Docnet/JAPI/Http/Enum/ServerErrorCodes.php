<?php

declare(strict_types=1);

namespace Docnet\JAPI\Http\Enum;

enum ServerErrorCodes: int
{
    case INTERNAL_SERVER_ERROR                = 500;
    case NOT_IMPLEMENTED                      = 501;
    case BAD_GATEWAY                          = 502;
    case SERVICE_UNAVAILABLE                  = 503;
    case GATEWAY_TIMEOUT                      = 504;
    case VERSION_NOT_SUPPORTED                = 505;
    case VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506; // RFC2295
    case INSUFFICIENT_STORAGE                 = 507; // RFC4918
    case LOOP_DETECTED                        = 508; // RFC5842
    case NOT_EXTENDED                         = 510; // RFC2774
    case NETWORK_AUTHENTICATION_REQUIRED      = 511; // RFC6585
}
