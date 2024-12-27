<?php

declare(strict_types=1);

namespace Docnet\JAPI\Http\Enum\HttpCodes;

enum InfoCodes: int
{
    case CONTINUE            = 100;
    case SWITCHING_PROTOCOLS = 101;
    case PROCESSING          = 102; // RFC2518
    case EARLY_HINTS         = 103; // RFC8297
}
