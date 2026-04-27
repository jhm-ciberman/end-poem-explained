// Reader chrome: drawer, theme toggle, index modal, keyboard navigation.

export default (config) => ({
    drawerOpen: false,
    indexOpen: false,
    poemSheetOpen: false,
    poemHidden: false,
    theme: 'light',
    prevUrl: config.prevUrl,
    nextUrl: config.nextUrl,

    init() {
        try {
            const saved = localStorage.getItem('epx-theme');
            this.theme = saved === 'dark' ? 'dark' : 'light';
        } catch (_) {
            this.theme = 'light';
        }
        try {
            this.poemHidden = localStorage.getItem('epx-poem-hidden') === 'true';
        } catch (_) {}
        this.applyTheme();

        this.onKey = (e) => {
            if (e.target && e.target.tagName === 'INPUT') return;
            if (this.indexOpen) {
                if (e.key === 'Escape') this.indexOpen = false;
                return;
            }
            if (this.poemSheetOpen) {
                if (e.key === 'Escape') this.poemSheetOpen = false;
                return;
            }
            if (e.key === 'ArrowRight' || e.key === ' ' || e.key === 'PageDown') {
                if (this.nextUrl) {
                    e.preventDefault();
                    window.Livewire.navigate(this.nextUrl);
                }
            } else if (e.key === 'ArrowLeft' || e.key === 'PageUp') {
                if (this.prevUrl) {
                    e.preventDefault();
                    window.Livewire.navigate(this.prevUrl);
                }
            } else if (e.key === 'i' || e.key === 'I') {
                this.indexOpen = true;
            } else if (e.key === 'Escape') {
                this.drawerOpen = false;
            }
        };
        window.addEventListener('keydown', this.onKey);
    },

    destroy() {
        window.removeEventListener('keydown', this.onKey);
    },

    toggleTheme() {
        this.theme = this.theme === 'light' ? 'dark' : 'light';
        this.applyTheme();
        try {
            localStorage.setItem('epx-theme', this.theme);
        } catch (_) {}
    },

    applyTheme() {
        document.documentElement.setAttribute('data-theme', this.theme);
    },

    togglePoem() {
        this.poemHidden = !this.poemHidden;
        try {
            localStorage.setItem('epx-poem-hidden', this.poemHidden);
        } catch (_) {}
        // The teleprompter recomputes offsets on resize; firing one nudges it
        // to re-centre after the column toggles back into view.
        this.$nextTick(() => {
            window.dispatchEvent(new Event('resize'));
        });
    },
});
