<?php

declare(strict_types=1);

namespace App\Data;

final readonly class PoemIndex
{
    /**
     * @param  list<Passage>  $passages
     * @param  list<PoemLine>  $lines  Every line from every passage, flattened in reading order.
     * @param  array<string, Passage>  $bySlug
     * @param  array<string, int>  $indexBySlug  Slug → index in `$passages`, for O(1) neighbour lookup.
     * @param  array<string, Voice>  $lineVoices  Line id → voice, for O(1) voice lookup.
     */
    public function __construct(
        public array $passages,
        public array $lines,
        public array $bySlug,
        public array $indexBySlug,
        public array $lineVoices,
    ) {}
}
