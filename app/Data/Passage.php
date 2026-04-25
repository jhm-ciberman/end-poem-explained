<?php

declare(strict_types=1);

namespace App\Data;

final readonly class Passage
{
    /**
     * @param  string  $slug  Filename basename, e.g. "0010-i-see-the-player-you-mean".
     * @param  list<PoemLine>  $lines  Lines this passage analyses; usually one, occasionally two.
     * @param  string  $fragment  Line texts joined by newline, used for inline rendering above the analysis.
     * @param  string  $analysis  Already-rendered HTML for the analysis prose body.
     * @param  bool  $final  True for the closing "Wake up" passage; triggers the final overlay.
     */
    public function __construct(
        public string $slug,
        public array $lines,
        public string $fragment,
        public string $analysis,
        public bool $final,
    ) {}
}
