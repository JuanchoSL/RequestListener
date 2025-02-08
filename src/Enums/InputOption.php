<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Enums;

enum InputOption: string
{
    case VOID = 'void';
    case SINGLE = 'single';
    case MULTI = 'multiple';
}