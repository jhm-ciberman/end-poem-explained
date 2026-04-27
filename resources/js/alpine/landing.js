// Landing form. The name lives in localStorage; this Alpine component
// reads any saved name on entry, validates the input, and routes the
// visitor to whichever of the two modes (poem or explanation) they pick.

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
    firstUrl: '',
    poemUrl: '',

    init() {
        this.firstUrl = this.$root.dataset.firstUrl || '/';
        this.poemUrl = this.$root.dataset.poemUrl || '/poem';
        const saved = readSaved();
        if (saved) {
            this.name = saved;
        }
    },

    goTo(url) {
        const trimmed = this.name.trim();
        if (!trimmed) return;
        writeSaved(trimmed);
        window.Livewire.navigate(url);
    },
});
