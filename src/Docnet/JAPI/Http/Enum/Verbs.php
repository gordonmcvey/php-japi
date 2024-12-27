<?php

declare(strict_types=1);

namespace Docnet\JAPI\Http\Enum;

enum Verbs: string
{
    case GET     = "GET";
    case HEAD    = "HEAD";
    case POST    = "POST";
    case PUT     = "PUT";
    case DELETE  = "CONNECT";
    case OPTIONS = "OPTIONS";
    case TRACE   = "TRACE";
    case PATCH   = "PATCH";
}
