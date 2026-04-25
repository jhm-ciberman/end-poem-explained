// Livewire 4 ships its own Alpine bundle. We register Alpine data factories
// for the Reader, Teleprompter, and Glitch components on `alpine:init`.

const GLITCH_POOL = '*?§*?§*?§!@#$%&/\\|<>{}[]+=~^_-:;.,abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
const STATIC_POOL = '*?§';

function randGlitch(len) {
    let out = '';
    for (let i = 0; i < len; i++) {
        out += GLITCH_POOL.charAt(Math.floor(Math.random() * GLITCH_POOL.length));
    }
    return out;
}

function staticGlitch(len) {
    let out = '';
    for (let i = 0; i < len; i++) {
        out += STATIC_POOL.charAt(i % STATIC_POOL.length);
    }
    return out;
}

document.addEventListener('alpine:init', () => {
    /**
     * Single glitch span. Cycles random characters at ~12fps when active,
     * shows a stable `*?§` pattern when frozen. Width is stable across
     * renders because length is set once on init.
     *
     * If `lineId` is given, the glitch tracks its parent teleprompter and
     * freezes itself whenever its line is not the focused one — only the
     * focused line's corruption flickers, the periphery sits still.
     */
    window.Alpine.data('glitch', (length = 6, lineId = null) => ({
        text: '',
        timer: null,
        length,
        lineId,
        frozen: false,
        init() {
            this.frozen = this.lineId !== null && this.$root && typeof this.$root.isFocused === 'function'
                ? !this.$root.isFocused(this.lineId)
                : false;
            this.refresh();

            if (this.lineId !== null && this.$root && '$watch' in this) {
                // React to focus changes on the parent teleprompter.
                this.$watch('$root.focusLineId', () => {
                    const next = !this.$root.isFocused(this.lineId);
                    if (next !== this.frozen) {
                        this.frozen = next;
                        this.refresh();
                    }
                });
            }
        },
        refresh() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
            if (this.frozen) {
                this.text = staticGlitch(this.length);
                return;
            }
            this.text = randGlitch(this.length);
            this.timer = setInterval(() => {
                this.text = randGlitch(this.length);
            }, 80);
        },
        destroy() {
            if (this.timer) clearInterval(this.timer);
        },
    }));

    /**
     * Teleprompter for the right-hand poem column.
     *
     * Animates the stack's translateY so the focused line lands at the
     * vertical centre of the column, plus per-line opacity that falls off
     * with distance from the focus. A fractional focus index is interpolated
     * during navigation so opacity transitions look continuous.
     */
    window.Alpine.data('teleprompter', (config) => ({
        focusLineId: config.focusLineId,
        lineIds: config.lineIds,
        offset: 0,
        focusIndex: config.lineIds.indexOf(config.focusLineId),
        // Animation state.
        fromOffset: 0,
        fromFocus: 0,
        targetOffset: 0,
        targetFocus: 0,
        animStart: 0,
        rafId: null,

        opacityTable: [1, 0.42, 0.22, 0.12, 0.07, 0.04, 0.025],

        init() {
            this.fromFocus = this.focusIndex;
            this.targetFocus = this.focusIndex;
            this.recalc(true);
            // After fonts and layout settle, recompute so the first line is
            // perfectly centred.
            requestAnimationFrame(() => requestAnimationFrame(() => this.recalc(true)));
            if (document.fonts && document.fonts.ready) {
                document.fonts.ready.then(() => this.recalc(true));
            }

            this.onResize = () => this.recalc(true);
            window.addEventListener('resize', this.onResize);

            // Re-target whenever a wire:navigate brings in a new passage.
            // The teleprompter element itself is persisted, so this Alpine
            // instance survives across pages; we read the new focus line
            // out of the freshly-swapped DOM each time navigation finishes.
            this.onNavigated = () => this.syncFocusFromDom();
            document.addEventListener('livewire:navigated', this.onNavigated);
            // Run once on first load too, in case the data div renders
            // with a different line than the x-data initial value.
            this.syncFocusFromDom();
        },

        destroy() {
            if (this.rafId) cancelAnimationFrame(this.rafId);
            window.removeEventListener('resize', this.onResize);
            document.removeEventListener('livewire:navigated', this.onNavigated);
        },

        syncFocusFromDom() {
            const el = document.getElementById('passage-focus');
            const next = el?.dataset.lineId;
            if (next && next !== this.focusLineId) {
                this.focusLineId = next;
                this.recalc();
            }
        },

        recalc(snap = false) {
            const col = this.$refs.col;
            const stack = this.$refs.stack;
            const el = stack?.querySelector(`[data-line-id="${CSS.escape(this.focusLineId)}"]`);
            if (!col || !stack || !el) return;

            const lineCenter = el.offsetTop + el.offsetHeight / 2;
            const colCenter = col.clientHeight / 2;
            const newTarget = colCenter - lineCenter;
            const newFocus = this.lineIds.indexOf(this.focusLineId);

            if (snap) {
                this.offset = newTarget;
                this.focusIndex = newFocus;
                this.fromOffset = newTarget;
                this.fromFocus = newFocus;
                this.targetOffset = newTarget;
                this.targetFocus = newFocus;
                return;
            }

            this.fromOffset = this.offset;
            this.fromFocus = this.focusIndex;
            this.targetOffset = newTarget;
            this.targetFocus = newFocus;
            this.animStart = performance.now();
            if (this.rafId) cancelAnimationFrame(this.rafId);
            this.tick();
        },

        tick() {
            const DURATION = 750;
            const easeInOut = (t) => (t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2);
            const now = performance.now();
            const elapsed = now - this.animStart;
            const t = Math.min(1, elapsed / DURATION);
            const e = easeInOut(t);
            this.offset = this.fromOffset + (this.targetOffset - this.fromOffset) * e;
            this.focusIndex = this.fromFocus + (this.targetFocus - this.fromFocus) * e;
            if (t < 1) {
                this.rafId = requestAnimationFrame(() => this.tick());
            } else {
                this.rafId = null;
            }
        },

        opacityFor(lineId) {
            const idx = this.lineIds.indexOf(lineId);
            const distance = Math.abs(idx - this.focusIndex);
            const lo = Math.min(Math.floor(distance), 6);
            const hi = Math.min(Math.ceil(distance), 6);
            const frac = distance - Math.floor(distance);
            return this.opacityTable[lo] + (this.opacityTable[hi] - this.opacityTable[lo]) * frac;
        },

        isFocused(lineId) {
            return lineId === this.focusLineId;
        },
    }));

    /**
     * Reader chrome: drawer, theme toggle, index modal, keyboard nav.
     */
    window.Alpine.data('readerChrome', (config) => ({
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
    }));
});
