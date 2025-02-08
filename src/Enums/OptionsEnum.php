<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Enums;

enum OptionsEnum: string
{
    case CLI = 'CLI';
    case HEAD = 'HEAD';
    case GET = 'GET';
    case POST = 'POST';
    case PUT = 'PUT';
    case PATCH = 'PATCH';
    case DELETE = 'DELETE';
    case OPTIONS = 'OPTIONS';
    case TRACE = 'TRACE';
}