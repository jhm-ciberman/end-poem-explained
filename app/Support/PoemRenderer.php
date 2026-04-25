<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Substitutes `{{name}}` and turns `{{corrupted}}` tokens into Alpine spans.
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
    public static function inline(string $text, string $name, string $where = 'poem', ?string $lineId = null): HtmlString
    {
        $named = str_replace('{{name}}', e($name), $text);

        $parts = preg_split('/(\{\{corrupted\}\})/', $named, -1, PREG_SPLIT_DELIM_CAPTURE);
        $out = '';

        foreach ($parts as $i => $part) {
            if ($part === '{{corrupted}}') {
                // Length is derived from the text and token position so it
                // stays the same across re-renders, keeping the line width
                // stable while the characters cycle.
                $length = 5 + ((mb_strlen($text) + $i) % 5);
                $out .= self::glitchSpan($length, $where, $lineId);
            } else {
                $out .= $part === '' ? '' : e($part);
            }
        }

        return new HtmlString($out);
    }

    /**
     * Render an analysis body that already contains HTML markup.
     *
     * Substitutes `{{name}}` and replaces `{{corrupted}}` tokens; the
     * surrounding `<p>`, `<em>`, and `<code>` markup is preserved verbatim.
     */
    public static function analysis(string $html, string $name): HtmlString
    {
        $substituted = str_replace('{{name}}', e($name), $html);

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
