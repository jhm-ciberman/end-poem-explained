<?php

declare(strict_types=1);

namespace App\Data;

/**
 * Identifies one of the two unnamed voices that speak the End Poem.
 */
enum Voice: string
{
    case Cyan = 'cyan';
    case Green = 'green';
}
