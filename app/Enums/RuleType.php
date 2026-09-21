<?php

namespace App\Enums;

enum RuleType: string
{
    case Exist = 'exist';
    case Count = 'count';
    case Length = 'length';
    case TextMatch = 'text_match';
    case Regex = 'regex';
    case CompareAmp = 'compare_amp';
    case JsonLd = 'json_ld';
}
