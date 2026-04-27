<?php

declare(strict_types=1);

namespace App\Data;

final readonly class Landmark
{
    /**
     * @param  string  $slug  Passage slug this landmark points to.
     * @param  string  $label  Short name shown in the index entry and reader chrome.
     */
    public function __construct(
        public string $slug,
        public string $label,
    ) {}
}
