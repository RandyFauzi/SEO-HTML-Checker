<?php

namespace App\Enums;

enum CheckStatus: string
{
    case Passed = 'passed';
    case Warning = 'warning';
    case Error = 'error';
    case Skipped = 'skipped';
}
