<?php

declare(strict_types=1);

namespace App\Data;

final readonly class PoemLine
{
    /**
     * @param  string  $id  Stable id used by the teleprompter to target this line.
     * @param  string  $text  As-authored text with `{{name}}` and `{{corrupted}}` tokens left intact.
     */
    public function __construct(
        public string $id,
        public Voice $voice,
        public string $text,
    ) {}
}
