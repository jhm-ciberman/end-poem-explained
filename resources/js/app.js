// Livewire 4 ships its own Alpine bundle, so we only need to register our
// data factories on `alpine:init`. Each component lives in its own file
// under `./alpine/` and exports a default factory function.

import glitch from './alpine/glitch.js';
import landing from './alpine/landing.js';
import readerChrome from './alpine/reader-chrome.js';
import teleprompter from './alpine/teleprompter.js';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('glitch', glitch);
    window.Alpine.data('landing', landing);
    window.Alpine.data('teleprompter', teleprompter);
    window.Alpine.data('readerChrome', readerChrome);
});

// Fill every <span data-player-name></span> with the player's saved name.
// Runs on the initial page load and after every wire:navigate.
function syncPlayerName() {
    let name = '';
    try {
        name = localStorage.getItem('epx-name') || '';
    } catch (_) {}
    if (!name) return;
    document.querySelectorAll('[data-player-name]').forEach((el) => {
        el.textContent = name;
    });
}

document.addEventListener('livewire:navigated', syncPlayerName);
