<?php

declare(strict_types=1);

namespace App\Data;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Yaml\Yaml;

/**
 * Loader for the poem passages stored as markdown files in `resources/pages/`.
 *
 * Filenames carry a four-digit numeric prefix (`0010-…`, `0020-…`) that
 * defines reading order. The prefix increments by ten so new passages can
 * be inserted between existing ones without renumbering.
 *
 * Tokens left intact in the loaded text:
 *   - `{{name}}`      — substituted by the rendering layer.
 *   - `{{corrupted}}` — rendered as a live glitch span by the rendering layer.
 */
final class Poem
{
    /** Per-request memoised payload. */
    private static ?PoemIndex $index = null;

    /**
     * Every passage in reading order.
     *
     * @return list<Passage>
     */
    public static function passages(): array
    {
        return self::index()->passages;
    }

    /**
     * Every poem line in reading order, flattened across passages.
     *
     * @return list<PoemLine>
     */
    public static function lines(): array
    {
        return self::index()->lines;
    }

    /**
     * Lines grouped into source-poem paragraphs, in reading order.
     *
     * @return list<Paragraph>
     */
    public static function paragraphs(): array
    {
        return self::index()->paragraphs;
    }

    /**
     * Named jumps shown in the reader's index modal.
     *
     * Labelled by name rather than position to spare the reader the "47 of
     * 117" anxiety a numeric progress display would create. Landmarks whose
     * target slug is missing on disk are silently dropped.
     *
     * @return list<array{slug: string, label: string}>
     */
    public static function landmarks(): array
    {
        $landmarks = [
            ['slug' => '0010-i-see-the-player-you-mean', 'label' => 'The opening'],
            ['slug' => '0020-the-name',                  'label' => 'Your name, first time'],
            ['slug' => '0130-a-million-others',          'label' => 'First breakage'],
            ['slug' => '0250-sometimes-i-do-not-care',   'label' => 'Almost telling you'],
            ['slug' => '0380-player-of-games',           'label' => 'Player of games'],
            ['slug' => '0400-take-a-breath-now',         'label' => 'Take a breath'],
            ['slug' => '0480-who-are-we',                'label' => 'Who are we'],
            ['slug' => '0660-once-upon-a-time',          'label' => 'Once upon a time'],
            ['slug' => '0670-the-player-was-you',        'label' => 'Your name, last time'],
            ['slug' => '1030-i-love-you',                'label' => 'I love you'],
            ['slug' => '1160-you-are-the-player-final',  'label' => 'You are the player'],
            ['slug' => '1170-wake-up',                   'label' => 'Wake up'],
        ];

        $known = self::index()->bySlug;

        return collect($landmarks)
            ->filter(fn (array $lm): bool => isset($known[$lm['slug']]))
            ->values()
            ->all();
    }

    /**
     * Look up a passage by slug.
     */
    public static function passage(string $slug): ?Passage
    {
        return self::index()->bySlug[$slug] ?? null;
    }

    /**
     * The voice that speaks a given line.
     */
    public static function voiceForLine(string $lineId): ?Voice
    {
        return self::index()->lineVoices[$lineId] ?? null;
    }

    /**
     * The slug of the first passage. Used by the landing page to start a read.
     */
    public static function firstSlug(): string
    {
        return self::passages()[0]->slug;
    }

    /**
     * Resolve the previous and next passage slugs around a given one.
     *
     * @return array{prev: ?string, next: ?string}
     */
    public static function neighbours(string $slug): array
    {
        $index = self::index();
        $idx = $index->indexBySlug[$slug] ?? null;
        if ($idx === null) {
            return ['prev' => null, 'next' => null];
        }

        return [
            'prev' => $idx > 0 ? $index->passages[$idx - 1]->slug : null,
            'next' => $idx < count($index->passages) - 1 ? $index->passages[$idx + 1]->slug : null,
        ];
    }

    /**
     * Load and parse all passage files, building the lookup tables once.
     *
     * Cached forever in non-local environments. In local dev the parse runs
     * on every request so file edits show up immediately.
     */
    private static function index(): PoemIndex
    {
        return self::$index ??= app()->isLocal()
            ? self::load()
            : Cache::rememberForever('poem.index', self::load(...));
    }

    /**
     * Read every passage file from disk and assemble the lookup tables.
     */
    private static function load(): PoemIndex
    {
        $directory = resource_path('pages');
        $files = glob($directory.'/*.md') ?: [];
        sort($files);

        if ($files === []) {
            throw new RuntimeException("No passage files found in {$directory}");
        }

        $finalSlug = basename(end($files), '.md');
        reset($files);

        $passages = [];
        $allLines = [];
        $bySlug = [];
        $indexBySlug = [];
        $lineVoices = [];

        foreach ($files as $idx => $path) {
            $slug = basename($path, '.md');
            $passage = self::parseFile($path, $slug, $slug === $finalSlug);

            $passages[] = $passage;
            $bySlug[$slug] = $passage;
            $indexBySlug[$slug] = $idx;

            foreach ($passage->lines as $line) {
                $allLines[] = $line;
                $lineVoices[$line->id] = $line->voice;
            }
        }

        return new PoemIndex(
            passages: $passages,
            lines: $allLines,
            paragraphs: self::groupIntoParagraphs($allLines),
            bySlug: $bySlug,
            indexBySlug: $indexBySlug,
            lineVoices: $lineVoices,
        );
    }

    /**
     * Walk the flat line list and collapse consecutive lines that share a
     * paragraph slug into a single Paragraph.
     *
     * @param  list<PoemLine>  $lines
     * @return list<Paragraph>
     */
    private static function groupIntoParagraphs(array $lines): array
    {
        $paragraphs = [];
        $bucket = [];
        $bucketSlug = null;
        $bucketVoice = null;

        $flush = function () use (&$paragraphs, &$bucket, &$bucketSlug, &$bucketVoice): void {
            if ($bucket === []) {
                return;
            }
            $paragraphs[] = new Paragraph(
                slug: $bucketSlug,
                voice: $bucketVoice,
                lines: $bucket,
            );
            $bucket = [];
            $bucketSlug = null;
            $bucketVoice = null;
        };

        foreach ($lines as $line) {
            if ($bucketSlug !== null && $bucketSlug === $line->paragraph) {
                $bucket[] = $line;

                continue;
            }
            $flush();
            $bucket = [$line];
            $bucketSlug = $line->paragraph;
            $bucketVoice = $line->voice;
        }
        $flush();

        return $paragraphs;
    }

    /**
     * Parse a single passage file: split frontmatter from body, render the
     * body as HTML, and assign a stable id to each line.
     */
    private static function parseFile(string $path, string $slug, bool $final): Passage
    {
        $contents = str(file_get_contents($path) ?: throw new RuntimeException("Cannot read passage file {$path}"));

        if (! $contents->startsWith("---\n")) {
            throw new RuntimeException("Passage {$slug} is missing YAML frontmatter");
        }

        $rest = $contents->after("---\n");
        $frontmatter = Yaml::parse((string) $rest->before("\n---\n")) ?? [];
        $body = (string) $rest->after("\n---\n");

        if (! isset($frontmatter['lines']) || ! is_array($frontmatter['lines'])) {
            throw new RuntimeException("Passage {$slug} is missing a `lines` array");
        }

        $prefix = Str::before($slug, '-');
        $lines = [];
        foreach ($frontmatter['lines'] as $i => $raw) {
            $voice = Voice::tryFrom($raw['voice'] ?? '');
            if ($voice === null) {
                throw new RuntimeException("Passage {$slug} line {$i} has invalid voice");
            }
            $id = "{$prefix}-{$i}";
            $lines[] = new PoemLine(
                id: $id,
                voice: $voice,
                text: (string) ($raw['text'] ?? ''),
                paragraph: (string) ($raw['paragraph'] ?? $id),
            );
        }

        return new Passage(
            slug: $slug,
            lines: $lines,
            fragment: collect($lines)->pluck('text')->implode("\n"),
            analysis: (string) str($body)->trim()->markdown(),
            final: $final,
        );
    }
}
