<?php

declare(strict_types=1);

namespace Docnet\JAPI\Http\Enum;

enum RedirectCodes: int
{
    case MULTIPLE_CHOICES     = 300;
    case MOVED_PERMANENTLY    = 301;
    case FOUND                = 302;
    case SEE_OTHER            = 303;
    case NOT_MODIFIED         = 304;
    case USE_PROXY            = 305;
    case RESERVED             = 306; // Was Switch Proxy
    case TEMPORARY_REDIRECT   = 307;
    case PERMANENTLY_REDIRECT = 308; // RFC7238
}
