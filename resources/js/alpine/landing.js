// Landing form. The name lives in localStorage; this Alpine component
// owns reading the saved name on entry, validating non-empty input, and
// kicking off the wire:navigate to the first passage on submit.

const STORAGE_KEY = 'epx-name';

function readSaved() {
    try {
        return localStorage.getItem(STORAGE_KEY) || '';
    } catch (_) {
        return '';
    }
}

function writeSaved(name) {
    try {
        localStorage.setItem(STORAGE_KEY, name);
    } catch (_) {}
}

export default () => ({
    name: '',
    savedName: '',
    firstUrl: '',

    init() {
        this.firstUrl = this.$root.dataset.firstUrl || '/';
        this.savedName = readSaved();
        if (this.savedName) {
            this.name = this.savedName;
        }
    },

    submit() {
        const trimmed = this.name.trim();
        if (!trimmed) return;
        writeSaved(trimmed);
        window.Livewire.navigate(this.firstUrl);
    },

    resume() {
        if (this.savedName) {
            window.Livewire.navigate(this.firstUrl);
        }
    },
});
