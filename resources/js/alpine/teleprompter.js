// Teleprompter for the right-hand poem column.
//
// Centers the focused line at the column's vertical midpoint and applies a
// per-paragraph opacity falloff so the focused paragraph reads at full
// clarity and surrounding paragraphs fade with distance. Animations are
// rAF-driven; both the offset and the (fractional) paragraph index
// interpolate during navigation so the falloff transitions smoothly.

const DURATION = 750;
// Softer falloff: distant paragraphs stay legible enough to read as "the
// poem continues above and below me," not just "there's some fog."
const OPACITY_BY_DISTANCE = [1, 0.7, 0.5, 0.36, 0.26, 0.19, 0.13];

const easeInOut = (t) => (t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2);

export default (config) => ({
    // The line we centre on. For multi-line passages, this is the first line.
    focusLineId: config.focusLineId,
    // Every line in the focused passage; drives `isFocused()` so multi-line
    // passages can mark all their lines, not only the centred one.
    focusLineIds: config.focusLineIds || [config.focusLineId],
    // Every paragraph touched by the focused passage; those stay at full
    // opacity even when they aren't the paragraph we're animating toward.
    focusParagraphSlugs: config.focusParagraphSlugs || [],
    paragraphSlugs: config.paragraphSlugs,
    offset: 0,
    paragraphIndex: 0,
    // Animation state.
    fromOffset: 0,
    fromParagraphIndex: 0,
    targetOffset: 0,
    targetParagraphIndex: 0,
    animStart: 0,
    rafId: null,

    init() {
        this.paragraphIndex = this.paragraphIndexForLine(this.focusLineId);
        this.fromParagraphIndex = this.paragraphIndex;
        this.targetParagraphIndex = this.paragraphIndex;
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
        if (!el) return;
        const lineIds = (el.dataset.lineIds || el.dataset.lineId || '').split(',').filter(Boolean);
        const paragraphSlugs = (el.dataset.paragraphSlugs || '').split(',').filter(Boolean);
        const primary = lineIds[0];
        if (!primary || primary === this.focusLineId) return;

        this.focusLineId = primary;
        this.focusLineIds = lineIds;
        this.focusParagraphSlugs = paragraphSlugs;
        this.recalc();
    },

    paragraphIndexForLine(lineId) {
        const el = this.$refs.stack?.querySelector(`[data-line-id="${CSS.escape(lineId)}"]`);
        const slug = el?.dataset.paragraph;
        const idx = slug ? this.paragraphSlugs.indexOf(slug) : -1;
        return idx >= 0 ? idx : 0;
    },

    recalc(snap = false) {
        const col = this.$refs.col;
        const stack = this.$refs.stack;
        const el = stack?.querySelector(`[data-line-id="${CSS.escape(this.focusLineId)}"]`);
        if (!col || !stack || !el) return;

        const lineCenter = el.offsetTop + el.offsetHeight / 2;
        const colCenter = col.clientHeight / 2;
        const newOffset = colCenter - lineCenter;
        const newParagraphIdx = this.paragraphIndexForLine(this.focusLineId);

        if (snap) {
            this.offset = newOffset;
            this.paragraphIndex = newParagraphIdx;
            this.fromOffset = newOffset;
            this.fromParagraphIndex = newParagraphIdx;
            this.targetOffset = newOffset;
            this.targetParagraphIndex = newParagraphIdx;
            return;
        }

        this.fromOffset = this.offset;
        this.fromParagraphIndex = this.paragraphIndex;
        this.targetOffset = newOffset;
        this.targetParagraphIndex = newParagraphIdx;
        this.animStart = performance.now();
        if (this.rafId) cancelAnimationFrame(this.rafId);
        this.tick();
    },

    tick() {
        const elapsed = performance.now() - this.animStart;
        const t = Math.min(1, elapsed / DURATION);
        const e = easeInOut(t);
        this.offset = this.fromOffset + (this.targetOffset - this.fromOffset) * e;
        this.paragraphIndex = this.fromParagraphIndex + (this.targetParagraphIndex - this.fromParagraphIndex) * e;
        if (t < 1) {
            this.rafId = requestAnimationFrame(() => this.tick());
        } else {
            this.rafId = null;
        }
    },

    opacityForParagraph(slug) {
        if (this.focusParagraphSlugs.includes(slug)) return 1;
        const idx = this.paragraphSlugs.indexOf(slug);
        if (idx < 0) return 1;
        const distance = Math.abs(idx - this.paragraphIndex);
        const lo = Math.min(Math.floor(distance), 6);
        const hi = Math.min(Math.ceil(distance), 6);
        const frac = distance - Math.floor(distance);
        return OPACITY_BY_DISTANCE[lo] + (OPACITY_BY_DISTANCE[hi] - OPACITY_BY_DISTANCE[lo]) * frac;
    },

    isFocused(lineId) {
        return this.focusLineIds.includes(lineId);
    },
});
