@php
    /** @var string $firstSlug */
@endphp

<x-layouts.app>
    <div
        x-data="landing()"
        data-first-url="{{ route('reader', ['slug' => $firstSlug]) }}"
        data-poem-url="{{ route('full-poem') }}"
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

            <form @submit.prevent="goTo(firstUrl)" class="flex flex-col items-center gap-5 mt-8">
                <div class="w-full max-w-sm">
                    <input
                        x-model="name"
                        type="text"
                        maxlength="40"
                        autocomplete="off"
                        placeholder="Your name"
                        autofocus
                        class="font-serif text-[1.05rem] bg-bg-soft border border-transparent rounded-full text-ink py-[0.85rem] px-6 outline-none w-full text-center transition-colors focus:bg-bg focus:border-ink-very-faint placeholder:text-ink-faint"
                    />
                </div>

                <div class="flex flex-col sm:flex-row gap-3 w-full max-w-sm">
                    <button
                        type="button"
                        @click="goTo(poemUrl)"
                        :disabled="!name.trim()"
                        class="flex-1 font-sans text-[0.78rem] tracking-[0.16em] uppercase bg-transparent border border-ink-very-faint text-ink-soft py-3.5 px-5 rounded-full cursor-pointer transition-all hover:border-ink hover:text-ink disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:border-ink-very-faint disabled:hover:text-ink-soft"
                    >
                        Read the poem
                    </button>
                    <button
                        type="submit"
                        :disabled="!name.trim()"
                        class="flex-1 font-sans text-[0.78rem] tracking-[0.16em] uppercase bg-transparent border border-ink-very-faint text-ink-soft py-3.5 px-5 rounded-full cursor-pointer transition-all hover:border-ink hover:text-ink disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:border-ink-very-faint disabled:hover:text-ink-soft"
                    >
                        Read the explanation
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
