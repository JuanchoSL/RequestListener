<?php declare(strict_types=1);

namespace JuanchoSL\RequestListener\Enums;

enum InputOption: string
{
    case VOID = 'void';
    case BOOL = 'bool';
    case SINGLE = 'single';
    case SINGLE_INT = 'single_int';
    case SINGLE_NUMBER = 'single_number';
    case MULTI = 'multiple';
    case MULTI_INT = 'multiple_int';
    case MULTI_NUMBER = 'multiple_number';
}