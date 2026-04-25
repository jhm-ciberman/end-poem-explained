<?php

use Illuminate\Support\Facades\Cookie;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

new
#[Layout('components.layouts.app')]
class extends Component
{
    /** Name the visitor wants the poem to address them by. */
    #[Validate('required|string|max:40')]
    public string $name = '';

    /** Previously-saved name from a prior visit, or null on first arrival. */
    public ?string $savedName = null;

    /**
     * Pre-fill the form when the visitor is returning from a previous read.
     */
    public function mount(): void
    {
        $this->savedName = request()->cookie('epx_name');
        if ($this->savedName) {
            $this->name = $this->savedName;
        }
    }

    /**
     * Persist the new name in a long-lived cookie and open the first passage.
     */
    public function start(): void
    {
        $this->validate();

        Cookie::queue('epx_name', trim($this->name), 60 * 24 * 365);
        $this->redirectRoute('reader', ['slug' => \App\Data\Poem::firstSlug()], navigate: true);
    }

    /**
     * Continue the previous read without changing the saved name.
     */
    public function resume(): void
    {
        $this->redirectRoute('reader', ['slug' => \App\Data\Poem::firstSlug()], navigate: true);
    }
};
?>

<div class="min-h-screen grid place-items-center px-6 py-12 bg-bg">
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

        <form
            wire:submit="start"
            x-data="{ value: @js($name) }"
            class="flex flex-col items-center gap-3 mt-8"
        >
            <div class="relative w-full max-w-sm">
                <input
                    wire:model="name"
                    x-model="value"
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
                    :disabled="!value.trim()"
                    class="absolute right-[0.4rem] top-1/2 -translate-y-1/2 w-9 h-9 rounded-full bg-bg border-0 cursor-pointer text-ink-soft grid place-items-center transition-colors hover:bg-ink hover:text-bg disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:bg-bg disabled:hover:text-ink-soft"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" class="w-3.5 h-3.5">
                        <line x1="5" y1="12" x2="19" y2="12" />
                        <polyline points="13 6 19 12 13 18" />
                    </svg>
                </button>
            </div>

            @if ($savedName)
                <button
                    type="button"
                    wire:click="resume"
                    class="font-sans text-[0.82rem] text-ink-faint bg-transparent border-0 cursor-pointer mt-4 underline decoration-ink-very-faint underline-offset-4 hover:text-ink"
                >
                    continue as {{ $savedName }}
                </button>
            @endif
        </form>
    </div>
</div>
