<?php

declare(strict_types=1);

namespace Docnet\JAPI\Http\Enum;

enum SuccessCodes: int
{
    case OK                            = 200;
    case CREATED                       = 201;
    case ACCEPTED                      = 202;
    case NON_AUTHORITATIVE_INFORMATION = 203;
    case NO_CONTENT                    = 204;
    case RESET_CONTENT                 = 205;
    case PARTIAL_CONTENT               = 206;
    case MULTI_STATUS                  = 207; // RFC4918
    case ALREADY_REPORTED              = 208; // RFC5842
    case IM_USED                       = 226; // RFC3229
}
