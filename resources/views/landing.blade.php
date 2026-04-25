@php
    /** @var string $firstSlug */
@endphp

<x-layouts.app>
    <div
        x-data="landing()"
        data-first-url="{{ route('reader', ['slug' => $firstSlug]) }}"
        class="min-h-screen grid place-items-center px-6 py-12 bg-bg"
    >
        <div class="max-w-xl w-full text-center">
            <h1 class="font-serif text-[clamp(3.2rem,8vw,5rem)] font-semibold leading-none m-0 text-ink tracking-[-0.02em]">
                End Poem
            </h1>
            <p class="font-serif italic font-normal text-[clamp(1.1rem,2.4vw,1.5rem)] text-ink-soft mt-2 mb-0">
                explained, line by line
            </p>
            <p class="font-sans text-[0.78rem] tracking-[0.16em] uppercase text-ink-faint mt-5">
                A reading of Julian Gough's poem
            </p>

            <div class="font-serif text-ink-soft text-[1.05rem] leading-[1.7] mt-12 mb-10 mx-auto max-w-md text-left space-y-4">
                <p>When you finish Minecraft, the credits play a poem. Two voices speak about you. They call you by name. They tell you the universe loves you, and then they tell you to wake up.</p>
                <p>This site walks through it slowly, with commentary on what each line means and why it lands.</p>
                <p>The poem will speak your name. Tell it what to call you.</p>
            </div>

            <form @submit.prevent="submit" class="flex flex-col items-center gap-3 mt-8">
                <div class="relative w-full max-w-sm">
                    <input
                        x-model="name"
                        type="text"
                        maxlength="40"
                        autocomplete="off"
                        placeholder="Name"
                        autofocus
                        class="font-serif text-[1.05rem] bg-bg-soft border border-transparent rounded-full text-ink py-[0.85rem] pl-6 pr-12 outline-none w-full text-center transition-colors focus:bg-bg focus:border-ink-very-faint placeholder:text-ink-faint"
                    />
                    <button
                        type="submit"
                        aria-label="Begin"
                        :disabled="!name.trim()"
                        class="absolute right-[0.4rem] top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-bg border-0 cursor-pointer text-ink-soft grid place-items-center transition-colors hover:bg-ink hover:text-bg disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:bg-bg disabled:hover:text-ink-soft"
                    >
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-3.5 h-3.5">
                            <line x1="5" y1="12" x2="19" y2="12" />
                            <polyline points="13 6 19 12 13 18" />
                        </svg>
                    </button>
                </div>

                <button
                    type="button"
                    x-show="savedName"
                    x-cloak
                    @click="resume"
                    x-text="`continue as ${savedName}`"
                    class="font-sans text-[0.82rem] text-ink-faint bg-transparent border-0 cursor-pointer mt-4 underline decoration-ink-very-faint underline-offset-4 hover:text-ink"
                ></button>
            </form>
        </div>
    </div>
</x-layouts.app>
