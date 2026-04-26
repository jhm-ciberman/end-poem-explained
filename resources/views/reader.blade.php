@php
    use App\Data\Poem;
    use App\Data\Voice;
    use App\Support\PoemRenderer;

    /** @var string $slug */

    $passage = Poem::passage($slug);
    $paragraphs = Poem::paragraphs();
    $landmarks = Poem::landmarks();

    $neighbours = Poem::neighbours($slug);
    $prevUrl = $neighbours['prev'] ? route('reader', ['slug' => $neighbours['prev']]) : null;
    $nextUrl = $neighbours['next'] ? route('reader', ['slug' => $neighbours['next']]) : null;

    $firstLine = $passage->lines[0] ?? null;
    $primaryVoice = $firstLine?->voice;
    $focusLineId = $firstLine?->id ?? '';
    $focusLineIds = collect($passage->lines)->pluck('id')->all();
    $focusParagraphSlugs = collect($passage->lines)->pluck('paragraph')->unique()->values()->all();
    $paragraphSlugs = collect($paragraphs)->pluck('slug')->all();

    // Precompute the passage that owns each line so the teleprompter loop
    // below can resolve clickable jump targets in O(1).
    $passageByLineId = [];
    foreach (Poem::passages() as $p) {
        foreach ($p->lines as $l) {
            $passageByLineId[$l->id] = $p->slug;
        }
    }

    $voiceTextClass = fn (?Voice $v) => match ($v) {
        Voice::Cyan => 'text-voice-cyan',
        Voice::Green => 'text-voice-green',
        default => '',
    };
@endphp

<x-layouts.app :title="'End Poem Explained — ' . $passage->slug">
    {{-- Reader needs a player name; fall back to landing if missing. --}}
    <script>
        if (!localStorage.getItem('epx-name')) {
            window.location.replace('/');
        }
    </script>

    <div
        x-data="readerChrome({
            prevUrl: @js($prevUrl),
            nextUrl: @js($nextUrl),
        })"
    >
        {{-- Floating menu top-left --}}
        <button
            type="button"
            x-show="!drawerOpen"
            @click="drawerOpen = true"
            aria-label="Open menu"
            class="fixed top-6 left-6 z-50 w-10 h-10 rounded-full bg-bg-soft border-0 cursor-pointer grid place-items-center text-ink-soft hover:bg-rule hover:text-ink transition-colors"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4">
                <line x1="4" y1="7" x2="20" y2="7" />
                <line x1="4" y1="12" x2="20" y2="12" />
                <line x1="4" y1="17" x2="20" y2="17" />
            </svg>
        </button>

        {{-- Drawer (fans out from the menu trigger) --}}
        <div
            x-show="drawerOpen"
            x-cloak
            x-transition:enter="animate-drawer-in"
            class="fixed top-6 left-6 z-60 bg-bg-soft rounded-3xl px-2 py-3 flex flex-col gap-1 shadow-[0_1px_3px_rgba(0,0,0,0.04)]"
        >
            <button type="button" @click="drawerOpen = false" aria-label="Close menu" class="w-10 h-10 rounded-full bg-transparent border-0 cursor-pointer text-ink grid place-items-center hover:bg-rule transition-colors">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4"><line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/></svg>
            </button>
            <div class="h-px bg-ink-very-faint mx-2 my-1"></div>
            <button type="button" @click="indexOpen = true; drawerOpen = false" title="Index" aria-label="Index" class="w-10 h-10 rounded-full bg-transparent border-0 cursor-pointer text-ink-soft grid place-items-center hover:bg-rule hover:text-ink transition-colors">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4"><line x1="9" y1="6" x2="20" y2="6"/><line x1="9" y1="12" x2="20" y2="12"/><line x1="9" y1="18" x2="20" y2="18"/><circle cx="5" cy="6" r="1.5" fill="currentColor"/><circle cx="5" cy="12" r="1.5" fill="currentColor"/><circle cx="5" cy="18" r="1.5" fill="currentColor"/></svg>
            </button>
            <button type="button" @click="toggleTheme()" title="Toggle theme" aria-label="Toggle theme" class="w-10 h-10 rounded-full bg-transparent border-0 cursor-pointer text-ink-soft grid place-items-center hover:bg-rule hover:text-ink transition-colors">
                <template x-if="theme === 'light'">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-4 h-4"><path d="M21 12.8A9 9 0 1111.2 3a7 7 0 009.8 9.8z"/></svg>
                </template>
                <template x-if="theme === 'dark'">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4"><circle cx="12" cy="12" r="4"/><path d="M12 3v2M12 19v2M3 12h2M19 12h2M5.6 5.6l1.4 1.4M17 17l1.4 1.4M5.6 18.4l1.4-1.4M17 7l1.4-1.4"/></svg>
                </template>
            </button>
            <button type="button" @click="changeName()" title="Change name" aria-label="Change name" class="w-10 h-10 rounded-full bg-transparent border-0 cursor-pointer text-ink-soft grid place-items-center hover:bg-rule hover:text-ink transition-colors">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-7 8-7s8 3 8 7"/></svg>
            </button>
            <a href="https://github.com/jhm-ciberman/end-poem-explained" target="_blank" rel="noopener noreferrer" title="Source on GitHub" aria-label="Source on GitHub" class="w-10 h-10 rounded-full bg-transparent border-0 cursor-pointer text-ink-soft grid place-items-center hover:bg-rule hover:text-ink transition-colors no-underline">
                <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path d="M12 .297a12 12 0 00-3.79 23.39c.6.11.82-.26.82-.58 0-.29-.01-1.05-.02-2.07-3.34.73-4.04-1.61-4.04-1.61-.55-1.39-1.34-1.76-1.34-1.76-1.09-.75.08-.73.08-.73 1.21.09 1.84 1.24 1.84 1.24 1.07 1.84 2.81 1.31 3.5 1 .11-.78.42-1.31.76-1.61-2.66-.3-5.47-1.33-5.47-5.93 0-1.31.47-2.38 1.24-3.22-.13-.3-.54-1.52.11-3.18 0 0 1.01-.32 3.3 1.23a11.5 11.5 0 016 0c2.29-1.55 3.3-1.23 3.3-1.23.65 1.66.24 2.88.12 3.18.77.84 1.23 1.91 1.23 3.22 0 4.61-2.81 5.62-5.49 5.92.43.37.81 1.1.81 2.22 0 1.61-.01 2.9-.01 3.3 0 .32.22.7.83.58A12 12 0 0012 .297z"/></svg>
            </a>
        </div>

        {{-- Two-column reader --}}
        <div class="grid grid-cols-1 md:grid-cols-2 min-h-screen bg-bg">
            {{-- LEFT — analysis --}}
            <div class="flex items-start md:justify-end px-6 pt-18 pb-12 md:px-20 md:py-24 md:sticky md:top-0 md:h-screen md:overflow-y-auto">
                <div class="max-w-lg w-full pb-12 md:pb-16">
                    <p class="font-sans text-[0.7rem] tracking-[0.16em] uppercase text-ink-faint mb-4 flex items-center gap-2.5">
                        <span @class([
                            'w-1.5 h-1.5 rounded-full',
                            'bg-voice-cyan' => $primaryVoice === Voice::Cyan,
                            'bg-voice-green' => $primaryVoice === Voice::Green,
                            'bg-ink-very-faint' => ! $primaryVoice,
                        ])></span>
                        <span>Passage</span>
                    </p>

                    <div class="font-pixel text-[1.2rem] leading-[1.4] text-ink-soft mb-8 pb-6 border-b border-rule text-pretty space-y-3">
                        @foreach ($passage->lines as $line)
                            <p @class(['m-0', $voiceTextClass($line->voice)])>
                                {!! PoemRenderer::inline($line->text, 'poem') !!}
                            </p>
                        @endforeach
                    </div>

                    <div class="font-serif text-[1.05rem] leading-[1.7] text-ink prose-poem">
                        {!! PoemRenderer::analysis($passage->analysis) !!}
                    </div>

                    <div class="mt-12 pt-6 border-t border-rule flex justify-between font-sans text-[0.78rem] tracking-[0.12em] uppercase text-ink-faint">
                        @if ($prevUrl)
                            <a href="{{ $prevUrl }}" wire:navigate class="bg-transparent border-0 cursor-pointer text-ink-soft py-2 px-0 hover:text-ink transition-colors no-underline">← Previous</a>
                        @else
                            <span class="text-ink-very-faint py-2">← Previous</span>
                        @endif
                        @if ($nextUrl)
                            <a href="{{ $nextUrl }}" wire:navigate class="bg-transparent border-0 cursor-pointer text-ink-soft py-2 px-0 hover:text-ink transition-colors no-underline">Next →</a>
                        @else
                            <span class="text-ink-very-faint py-2">Next →</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- RIGHT — teleprompter (hidden on mobile, persisted across passage --}}
            {{-- navigations so the focus glides smoothly instead of snapping). --}}
            @persist('teleprompter-col')
                <div
                    class="hidden md:block border-l border-rule px-16 sticky top-0 h-screen overflow-hidden"
                    x-data="teleprompter({
                        focusLineId: @js($focusLineId),
                        focusLineIds: @js($focusLineIds),
                        focusParagraphSlugs: @js($focusParagraphSlugs),
                        paragraphSlugs: @js($paragraphSlugs),
                    })"
                    x-ref="col"
                >
                    <div class="poem-fade is-top"></div>
                    <div class="poem-fade is-bottom"></div>

                    <div
                        x-ref="stack"
                        class="poem-stack font-pixel text-[1.35rem] leading-[1.55] max-w-lg"
                        :style="`transform: translateY(${offset}px)`"
                    >
                        @foreach ($paragraphs as $paragraph)
                            <p
                                wire:key="paragraph-{{ $paragraph->slug }}"
                                @class([
                                    'm-0 mb-[1.4rem] text-pretty',
                                    'pl-6' => $paragraph->voice === Voice::Green,
                                    $voiceTextClass($paragraph->voice),
                                ])
                                :style="`opacity: ${opacityForParagraph('{{ $paragraph->slug }}')}`"
                            >
                                @foreach ($paragraph->lines as $line)
                                    @php
                                        $linePassageSlug = $passageByLineId[$line->id] ?? null;
                                        $isClickable = $linePassageSlug && $linePassageSlug !== $slug;
                                        $clickUrl = $linePassageSlug ? route('reader', ['slug' => $linePassageSlug]) : null;
                                    @endphp
                                    <span
                                        data-line-id="{{ $line->id }}"
                                        data-paragraph="{{ $paragraph->slug }}"
                                        @class(['cursor-pointer' => $isClickable])
                                        :class="isFocused('{{ $line->id }}') && 'bg-current/10 box-decoration-clone rounded-sm px-1 -mx-1'"
                                        @if ($clickUrl)
                                            @click="window.Livewire.navigate('{{ $clickUrl }}')"
                                        @endif
                                    >{!! PoemRenderer::inline($line->text, 'poem', $line->id) !!}</span>{{ ' ' }}
                                @endforeach
                            </p>
                        @endforeach
                    </div>
                </div>
            @endpersist

            {{-- Outside @persist so each navigation renders a fresh value. --}}
            {{-- The persisted teleprompter reads this on `livewire:navigated`. --}}
            <div
                id="passage-focus"
                data-line-id="{{ $focusLineId }}"
                data-line-ids="{{ implode(',', $focusLineIds) }}"
                data-paragraph-slugs="{{ implode(',', $focusParagraphSlugs) }}"
                hidden
            ></div>
        </div>

        {{-- Index modal --}}
        <div
            x-show="indexOpen"
            x-cloak
            x-transition:enter="animate-fade-in"
            class="fixed inset-0 bg-black/40 dark:bg-black/70 backdrop-blur-md z-100 grid place-items-center p-8"
            @click.self="indexOpen = false"
        >
            <div class="bg-bg rounded-2xl max-w-md w-full px-8 pt-9 pb-7 max-h-[80vh] overflow-y-auto relative">
                <button @click="indexOpen = false" aria-label="Close" class="absolute top-4 right-4 bg-transparent border-0 cursor-pointer text-ink-faint w-8 h-8 rounded-full grid place-items-center hover:bg-bg-soft hover:text-ink transition-colors">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-4 h-4"><line x1="6" y1="6" x2="18" y2="18"/><line x1="18" y1="6" x2="6" y2="18"/></svg>
                </button>
                <p class="font-sans text-[0.7rem] tracking-[0.16em] uppercase text-ink-faint m-0 mb-5">
                    Jump to a passage
                </p>
                <ul class="list-none p-0 m-0 mb-5">
                    @foreach ($landmarks as $lm)
                        <li class="m-0">
                            <a
                                href="{{ route('reader', ['slug' => $lm['slug']]) }}"
                                wire:navigate
                                @click="indexOpen = false"
                                class="w-full bg-transparent border-0 font-serif text-base text-ink cursor-pointer py-3 flex justify-between items-baseline border-b border-rule text-left transition-[padding] hover:pl-1.5 no-underline"
                            >
                                <span>{{ $lm['label'] }}{{ $lm['slug'] === $slug ? ' — current' : '' }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- Final overlay (Wake up) --}}
        @if ($passage->final)
            <div
                x-data="{ shown: false }"
                x-init="setTimeout(() => shown = true, 2000)"
                x-show="shown"
                x-cloak
                x-transition:enter="animate-overlay-in"
                class="fixed inset-0 bg-bg grid place-items-center z-80"
            >
                <div class="max-w-md p-8 text-center">
                    <h2 class="font-serif italic font-medium text-[clamp(2.5rem,6vw,3.5rem)] m-0 mb-6 text-ink tracking-[-0.01em]">
                        Wake up, <span data-player-name></span>.
                    </h2>
                    <p class="text-ink-soft text-[1.05rem] leading-[1.7] m-0 mb-10">
                        That was the End Poem. You read it through.
                    </p>
                    <div>
                        <a
                            href="{{ route('reader', ['slug' => Poem::firstSlug()]) }}"
                            wire:navigate
                            class="font-sans text-[0.78rem] tracking-[0.16em] uppercase bg-transparent border border-ink-very-faint text-ink-soft py-3.5 px-7 rounded-full cursor-pointer transition-all hover:border-ink hover:text-ink no-underline inline-block"
                        >
                            Read again
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
