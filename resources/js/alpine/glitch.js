// Single glitch span. Cycles random characters at ~12fps when active and
// shows a stable `*?§` pattern when frozen. Width is stable across renders
// because length is set once on init.
//
// If `lineId` is given, the glitch tracks its parent teleprompter and freezes
// itself whenever its line is not the focused one — only the focused line's
// corruption flickers, the periphery sits still.

const ACTIVE_POOL = '*?§*?§*?§!@#$%&/\\|<>{}[]+=~^_-:;.,abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
const FROZEN_POOL = '*?§';

function rand(len) {
    let out = '';
    for (let i = 0; i < len; i++) {
        out += ACTIVE_POOL.charAt(Math.floor(Math.random() * ACTIVE_POOL.length));
    }
    return out;
}

function frozen(len) {
    let out = '';
    for (let i = 0; i < len; i++) {
        out += FROZEN_POOL.charAt(i % FROZEN_POOL.length);
    }
    return out;
}

export default (length = 6, lineId = null) => ({
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
            this.text = frozen(this.length);
            return;
        }
        this.text = rand(this.length);
        this.timer = setInterval(() => {
            this.text = rand(this.length);
        }, 80);
    },

    destroy() {
        if (this.timer) clearInterval(this.timer);
    },
});
