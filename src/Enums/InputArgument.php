<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Enums;

enum InputArgument: string
{
    case OPTIONAL = 'optional';
    case REQUIRED = 'required';
}