// Teleprompter for the right-hand poem column.
//
// Animates the stack's translateY so the focused line lands at the vertical
// centre of the column, plus per-line opacity that falls off with distance
// from the focus. A fractional focus index is interpolated during navigation
// so opacity transitions look continuous.

const DURATION = 750;
const OPACITY_BY_DISTANCE = [1, 0.42, 0.22, 0.12, 0.07, 0.04, 0.025];

const easeInOut = (t) => (t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2);

export default (config) => ({
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

        // Re-target whenever a wire:navigate brings in a new passage. The
        // teleprompter element itself is persisted, so this Alpine instance
        // survives across pages; we read the new focus line out of the
        // freshly-swapped DOM each time navigation finishes.
        this.onNavigated = () => this.syncFocusFromDom();
        document.addEventListener('livewire:navigated', this.onNavigated);
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
        const elapsed = performance.now() - this.animStart;
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
        return OPACITY_BY_DISTANCE[lo] + (OPACITY_BY_DISTANCE[hi] - OPACITY_BY_DISTANCE[lo]) * frac;
    },

    isFocused(lineId) {
        return lineId === this.focusLineId;
    },
});
