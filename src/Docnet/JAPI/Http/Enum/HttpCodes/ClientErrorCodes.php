<?php

declare(strict_types=1);

namespace Docnet\JAPI\Http\Enum\HttpCodes;

enum ClientErrorCodes: int
{
    case BAD_REQUEST                     = 400;
    case UNAUTHORIZED                    = 401;
    case PAYMENT_REQUIRED                = 402;
    case FORBIDDEN                       = 403;
    case NOT_FOUND                       = 404;
    case METHOD_NOT_ALLOWED              = 405;
    case NOT_ACCEPTABLE                  = 406;
    case PROXY_AUTHENTICATION_REQUIRED   = 407;
    case REQUEST_TIMEOUT                 = 408;
    case CONFLICT                        = 409;
    case GONE                            = 410;
    case LENGTH_REQUIRED                 = 411;
    case PRECONDITION_FAILED             = 412;
    case REQUEST_ENTITY_TOO_LARGE        = 413;
    case REQUEST_URI_TOO_LONG            = 414;
    case UNSUPPORTED_MEDIA_TYPE          = 415;
    case REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    case EXPECTATION_FAILED              = 417;
    case I_AM_A_TEAPOT                   = 418; // RFC2324
    case MISDIRECTED_REQUEST             = 421; // RFC7540
    case UNPROCESSABLE_ENTITY            = 422; // RFC4918
    case LOCKED                          = 423; // RFC4918
    case FAILED_DEPENDENCY               = 424; // RFC4918
    case TOO_EARLY                       = 425; // RFC-ietf-httpbis-replay-04
    case UPGRADE_REQUIRED                = 426; // RFC2817
    case PRECONDITION_REQUIRED           = 428; // RFC6585
    case TOO_MANY_REQUESTS               = 429; // RFC6585
    case REQUEST_HEADER_FIELDS_TOO_LARGE = 431; // RFC6585
    case UNAVAILABLE_FOR_LEGAL_REASONS   = 451; // RFC7725
}
