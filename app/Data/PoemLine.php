<?php

declare(strict_types=1);

namespace App\Data;

final readonly class PoemLine
{
    /**
     * @param  string  $id  Stable id used by the teleprompter to target this line.
     * @param  string  $text  As-authored text with `{{name}}` and `{{corrupted}}` tokens left intact.
     * @param  string  $paragraph  Slug of the source-poem paragraph this line belongs to. Lines that share a slug render as one paragraph in the reader. Defaults to the line's own id, making the line a singleton paragraph.
     */
    public function __construct(
        public string $id,
        public Voice $voice,
        public string $text,
        public string $paragraph,
    ) {}
}
