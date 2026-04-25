<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Replaces `{{name}}` with a JS-filled placeholder and `{{corrupted}}` with
 * Alpine glitch spans.
 *
 * The player's name lives in localStorage on the client; the server only
 * emits a `<span data-player-name></span>` marker that JS fills in.
 */
final class PoemRenderer
{
    /**
     * Render a poem fragment as inline HTML.
     *
     * Pass a teleprompter `$lineId` to wire each glitch span to its parent
     * teleprompter so it freezes when its line is not focused; pass null
     * for one-off renders that should always animate.
     */
    public static function inline(string $text, string $where = 'poem', ?string $lineId = null): HtmlString
    {
        $parts = preg_split('/(\{\{corrupted\}\})/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $out = '';

        foreach ($parts as $i => $part) {
            if ($part === '{{corrupted}}') {
                // Length is derived from the text and token position so it
                // stays the same across re-renders, keeping the line width
                // stable while the characters cycle.
                $length = 5 + ((mb_strlen($text) + $i) % 5);
                $out .= self::glitchSpan($length, $where, $lineId);
            } elseif ($part !== '') {
                $out .= e($part);
            }
        }

        // Done after escaping so the placeholder's tags survive intact.
        return new HtmlString(str_replace('{{name}}', self::namePlaceholder(), $out));
    }

    /**
     * Render an analysis body that already contains HTML markup.
     *
     * Substitutes `{{name}}` and replaces `{{corrupted}}` tokens; the
     * surrounding `<p>`, `<em>`, and `<code>` markup is preserved verbatim.
     */
    public static function analysis(string $html): HtmlString
    {
        $substituted = str_replace('{{name}}', self::namePlaceholder(), $html);

        $counter = 0;
        $rendered = preg_replace_callback(
            '/\{\{corrupted\}\}/',
            function () use (&$counter): string {
                $length = 4 + ($counter++ % 5) + 2;

                return self::glitchSpan($length, 'analysis', lineId: null);
            },
            $substituted
        );

        return new HtmlString($rendered ?? $substituted);
    }

    /**
     * The placeholder span that JS fills with the player's name on every
     * page render. Empty by default so unstyled fallback shows nothing
     * rather than the literal token.
     */
    private static function namePlaceholder(): string
    {
        return '<span data-player-name></span>';
    }

    /**
     * Build the markup for a single Alpine glitch span.
     */
    private static function glitchSpan(int $length, string $where, ?string $lineId): string
    {
        $lineArg = $lineId === null ? 'null' : "'".addslashes($lineId)."'";
        $whereEscaped = e($where);

        // The inner text mimics Gough's original `*?§` markers so readers
        // without JavaScript still see something where the language breaks.
        return sprintf(
            '<span class="glitch in-%s" x-data="glitch(%d, %s)" x-text="text">%s</span>',
            $whereEscaped,
            $length,
            $lineArg,
            str_repeat('*?§', (int) ceil($length / 3))
        );
    }
}
