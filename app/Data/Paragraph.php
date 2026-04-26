<?php

declare(strict_types=1);

namespace App\Data;

final readonly class Paragraph
{
    /**
     * @param  string  $slug  Stable id, either declared in a passage's frontmatter or derived from a singleton line's id.
     * @param  Voice  $voice  All lines in a paragraph share a voice.
     * @param  list<PoemLine>  $lines  In reading order.
     */
    public function __construct(
        public string $slug,
        public Voice $voice,
        public array $lines,
    ) {}
}
