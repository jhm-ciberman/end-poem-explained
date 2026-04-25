// Reader chrome: drawer, theme toggle, index modal, keyboard navigation.

export default (config) => ({
    drawerOpen: false,
    indexOpen: false,
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
        this.applyTheme();

        this.onKey = (e) => {
            if (e.target && e.target.tagName === 'INPUT') return;
            if (this.indexOpen) {
                if (e.key === 'Escape') this.indexOpen = false;
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

    changeName() {
        const fresh = prompt('Read as someone else?', '');
        if (fresh && fresh.trim()) {
            const t = fresh.trim();
            try {
                localStorage.setItem('epx-name', t);
            } catch (_) {}
            document.cookie = 'epx_name=' + encodeURIComponent(t) + '; path=/; max-age=31536000; SameSite=Lax';
            window.location.reload();
        }
    },
});
