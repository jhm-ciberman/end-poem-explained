@php
    use App\Data\Voice;
    use App\Support\PoemRenderer;

    /** @var list<\App\Data\Paragraph> $paragraphs */
    /** @var string $firstSlug */

    $voiceTextClass = fn (?Voice $v) => match ($v) {
        Voice::Cyan => 'text-voice-cyan',
        Voice::Green => 'text-voice-green',
        default => '',
    };
@endphp

<x-layouts.app :title="'End Poem Explained — the poem'">
    <div
        x-data="{
            theme: 'light',
            init() {
                try { this.theme = localStorage.getItem('epx-theme') === 'dark' ? 'dark' : 'light'; } catch (_) {}
                document.documentElement.setAttribute('data-theme', this.theme);
            },
            toggleTheme() {
                this.theme = this.theme === 'light' ? 'dark' : 'light';
                document.documentElement.setAttribute('data-theme', this.theme);
                try { localStorage.setItem('epx-theme', this.theme); } catch (_) {}
            },
        }"
        class="min-h-screen bg-bg"
    >
        {{-- Top chrome: back to landing, theme toggle. --}}
        <div class="fixed top-6 left-6 right-6 z-50 flex items-center justify-between pointer-events-none">
            <a
                href="{{ route('landing') }}"
                wire:navigate
                aria-label="Back"
                class="pointer-events-auto w-10 h-10 rounded-full bg-bg-soft text-ink-soft hover:bg-rule hover:text-ink transition-colors grid place-items-center no-underline"
            >
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4">
                    <line x1="19" y1="12" x2="5" y2="12" />
                    <polyline points="11 6 5 12 11 18" />
                </svg>
            </a>
            <button
                type="button"
                @click="toggleTheme()"
                aria-label="Toggle theme"
                class="pointer-events-auto w-10 h-10 rounded-full bg-bg-soft text-ink-soft hover:bg-rule hover:text-ink transition-colors grid place-items-center"
            >
                <template x-if="theme === 'light'">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M21 12.8A9 9 0 1111.2 3a7 7 0 009.8 9.8z"/></svg>
                </template>
                <template x-if="theme === 'dark'">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4"><circle cx="12" cy="12" r="4"/><path d="M12 3v2M12 19v2M3 12h2M19 12h2M5.6 5.6l1.4 1.4M17 17l1.4 1.4M5.6 18.4l1.4-1.4M17 7l1.4-1.4"/></svg>
                </template>
            </button>
        </div>

        <div class="px-6 pt-32 pb-24 md:pt-40 md:pb-32">
            <div class="max-w-2xl mx-auto">
                <p class="font-sans text-[0.7rem] tracking-[0.16em] uppercase text-ink-faint text-center mb-3">
                    The End Poem
                </p>
                <h1 class="font-serif italic font-medium text-[clamp(1.6rem,3vw,2rem)] text-center text-ink-soft mb-16 md:mb-24">
                    by Julian Gough
                </h1>

                <div class="font-pixel text-[1.25rem] md:text-[1.4rem] leading-[1.6] flex flex-col gap-16 md:gap-24 text-pretty">
                    @foreach ($paragraphs as $paragraph)
                        <p @class([
                            'm-0',
                            'pl-6 md:pl-10' => $paragraph->voice === Voice::Green,
                            $voiceTextClass($paragraph->voice),
                        ])>
                            @foreach ($paragraph->lines as $line)
                                {!! PoemRenderer::inline($line->text, 'poem', $line->id) !!}{{ ' ' }}
                            @endforeach
                        </p>
                    @endforeach
                </div>

                <div class="mt-20 md:mt-28 pt-8 border-t border-rule text-center">
                    <a
                        href="{{ route('reader', ['slug' => $firstSlug]) }}"
                        wire:navigate
                        class="font-sans text-[0.78rem] tracking-[0.16em] uppercase bg-transparent border border-ink-very-faint text-ink-soft py-3.5 px-7 rounded-full transition-all hover:border-ink hover:text-ink no-underline inline-block"
                    >
                        Read the explanation
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
