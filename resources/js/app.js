// Livewire 4 ships its own Alpine bundle, so we only need to register our
// data factories on `alpine:init`. Each component lives in its own file
// under `./alpine/` and exports a default factory function.

import glitch from './alpine/glitch.js';
import readerChrome from './alpine/reader-chrome.js';
import teleprompter from './alpine/teleprompter.js';

document.addEventListener('alpine:init', () => {
    window.Alpine.data('glitch', glitch);
    window.Alpine.data('teleprompter', teleprompter);
    window.Alpine.data('readerChrome', readerChrome);
});
