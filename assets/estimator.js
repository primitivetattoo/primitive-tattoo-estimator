(function () {
    'use strict';

    /* ============================================================
       CONFIG — loaded from WordPress settings via wp_localize_script
       Falls back to defaults if ptbEstConfig is not available
    ============================================================ */
    var cfg = (typeof ptbEstConfig !== 'undefined') ? ptbEstConfig : {};

    var WA_NUMBER = cfg.waNumber || '6281945737100';

    var PROMO = cfg.promo || {
        active: true,
        title: '3 Fine Line Tattoos',
        sub: 'Max 5cm each · Any placement · While promo lasts',
        price: 'IDR 1,000,000',
        url: '',
        waMsg: "Hi Primitive Tattoo Bali! I'd like to book the 3 Fine Line Tattoos for IDR 1,000,000 promo. Max 5cm each. Can I get more details and book a session?"
    };

    var STYLES = cfg.styles || [
        { name: 'Fine Line',   icon: '✦', desc: 'Delicate, precise linework',    mult: 1.2  },
        { name: 'Blackwork',   icon: '◼', desc: 'Bold black ink designs',         mult: 1.0  },
        { name: 'Mandala',     icon: '❋', desc: 'Geometric spiritual patterns',   mult: 1.3  },
        { name: 'Traditional', icon: '⚓', desc: 'Classic bold outlines & fills', mult: 0.95 },
        { name: 'Realism',     icon: '◎', desc: 'Photo-realistic detail work',    mult: 1.6  },
        { name: 'Watercolor',  icon: '◈', desc: 'Fluid painterly effects',        mult: 1.25 }
    ];

    var SIZES = cfg.sizes || [
        { name: 'Small',       sub: 'Coin-sized · up to 5cm',  base: [300000,  700000]  },
        { name: 'Medium',      sub: 'Palm-sized · 5–10cm',     base: [700000,  1400000] },
        { name: 'Large',       sub: 'Hand-sized · 10–20cm',    base: [1400000, 2800000] },
        { name: 'Extra Large', sub: 'Full panel · 20cm+',      base: [2800000, 6000000] }
    ];

    var PLACEMENTS = cfg.placements || [
        { name: 'Wrist',      mult: 1.0  },
        { name: 'Forearm',    mult: 1.0  },
        { name: 'Upper Arm',  mult: 1.0  },
        { name: 'Chest',      mult: 1.15 },
        { name: 'Back',       mult: 1.2  },
        { name: 'Ribcage',    mult: 1.25 },
        { name: 'Calf',       mult: 1.0  },
        { name: 'Thigh',      mult: 1.1  },
        { name: 'Neck',       mult: 1.3  },
        { name: 'Finger',     mult: 0.85 }
    ];

    var COMPLEXITY = cfg.complexity || [
        { label: 'Simple',   sub: 'Clean lines, minimal detail', mult: 1.0  },
        { label: 'Moderate', sub: 'Some shading or fills',       mult: 1.3  },
        { label: 'Detailed', sub: 'Heavy shading, fine detail',  mult: 1.65 }
    ];

    // Ensure multipliers are numbers (wp_localize_script sends strings)
    STYLES.forEach(function(s) { s.mult = parseFloat(s.mult); });
    PLACEMENTS.forEach(function(p) { p.mult = parseFloat(p.mult); });
    COMPLEXITY.forEach(function(c) { c.mult = parseFloat(c.mult); });
    SIZES.forEach(function(s) {
        if (s.base) {
            s.base[0] = parseInt(s.base[0], 10);
            s.base[1] = parseInt(s.base[1], 10);
        }
    });

    /* ============================================================
       STATE
    ============================================================ */
    var state = {
        step:       0,
        style:      null,
        size:       null,
        placement:  null,
        complexity: null,
        showResult: false,
        promoMode:  false
    };

    /* ============================================================
       HELPERS
    ============================================================ */
    function fmtIDR(n) {
        if (n >= 1000000) return (n / 1000000).toFixed(1) + 'M';
        return Math.round(n / 1000) + 'K';
    }

    function calcRange() {
        var sz = SIZES.find(function(x){ return x.name === state.size; });
        var sm = STYLES.find(function(x){ return x.name === state.style; }).mult;
        var pm = PLACEMENTS.find(function(x){ return x.name === state.placement; }).mult;
        var cm = COMPLEXITY.find(function(x){ return x.label === state.complexity; }).mult;
        return [
            Math.round(sz.base[0] * sm * pm * cm / 50000) * 50000,
            Math.round(sz.base[1] * sm * pm * cm / 50000) * 50000
        ];
    }

    function waLink(msg) {
        return 'https://wa.me/' + WA_NUMBER + '?text=' + encodeURIComponent(msg);
    }

    function promoLink() {
        if (PROMO.url) return PROMO.url;
        return waLink(PROMO.waMsg);
    }

    function filled() {
        return [state.style, state.size, state.placement, state.complexity];
    }

    /* ============================================================
       RENDER
    ============================================================ */
    function render() {
        var root = document.getElementById('ptb-estimator-root');
        if (!root) return;

        var html = '';

        /* Header */
        /* Header removed — use site widgets/Elementor for headline above the estimator */

        /* ---- PROMO MODE ---- */
        if (PROMO.active && state.promoMode) {
            html += '<div class="ptb-promo-result">'
                + '<p class="ptb-result-eyebrow">✦ Limited Promo Selected</p>'
                + '<p style="font-size:20px;font-weight:500;">' + PROMO.title + '</p>'
                + '<p style="font-size:13px;opacity:.55;margin:10px 0 0;line-height:1.7;">' + PROMO.sub + '</p>'
                + '<div class="ptb-promo-result-price">' + PROMO.price + '</div>'
                + '<p style="font-size:11px;opacity:.3;margin-bottom:24px;">Fixed promo price · No hidden fees</p>'
                + '<a class="ptb-btn-gold" href="' + promoLink() + '" target="_blank" rel="noopener">Book This Promo on WhatsApp</a><br>'
                + '<button class="ptb-btn-ghost" onclick="ptbBackToEstimator()">← Back to estimator</button>'
                + '</div>';
            root.innerHTML = html;
            return;
        }

        /* ---- PROMO BANNER ---- */
        if (PROMO.active && !state.promoMode) {
            html += '<div class="ptb-promo-banner" onclick="ptbSelectPromo()" role="button" tabindex="0">'
                + '<div class="ptb-promo-glow"></div>'
                + '<div>'
                + '<p class="ptb-promo-banner-label">✦ Limited Promo</p>'
                + '<p class="ptb-promo-banner-title">' + PROMO.title + '</p>'
                + '<p class="ptb-promo-banner-sub">' + PROMO.sub + '</p>'
                + '</div>'
                + '<div>'
                + '<p class="ptb-promo-banner-price">' + PROMO.price + '</p>'
                + '<p class="ptb-promo-banner-cta">Book this deal →</p>'
                + '</div>'
                + '</div>';
        }

        /* ---- DIVIDER ---- */
        html += '<div class="ptb-divider">'
            + '<div class="ptb-divider-line"></div>'
            + '<span class="ptb-divider-text">Or estimate a custom piece</span>'
            + '<div class="ptb-divider-line"></div>'
            + '</div>';

        /* ---- PROGRESS DOTS ---- */
        var f = filled();
        html += '<div class="ptb-progress">';
        for (var i = 0; i < 4; i++) {
            var isActive = (state.step === i && !state.showResult);
            var isDone   = (f[i] !== null);
            var cls = isActive ? 'ptb-dot--active' : (isDone ? 'ptb-dot--done' : 'ptb-dot--inactive');
            html += '<div class="ptb-dot ' + cls + '" onclick="ptbGoStep(' + i + ')" role="button" tabindex="0">'
                + (isDone && !isActive ? '✓' : (i + 1))
                + '</div>';
            if (i < 3) {
                html += '<div class="ptb-dot-line' + (isDone ? ' ptb-dot-line--done' : '') + '"></div>';
            }
        }
        html += '</div>';

        /* ---- STEPS ---- */
        if (!state.showResult) {

            html += '<div class="ptb-step">';

            if (state.step === 0) {
                html += '<p class="ptb-question">What style are you after?</p><div class="ptb-grid-styles">';
                STYLES.forEach(function(s) {
                    var active = state.style === s.name ? ' ptb-card--active' : '';
                    html += '<div class="ptb-card' + active + '" onclick="ptbPick(\'style\',\'' + s.name.replace(/'/g, "\\'") + '\',1)" role="button" tabindex="0">'
                        + '<span class="ptb-card-icon">' + s.icon + '</span>'
                        + '<span class="ptb-card-name">' + s.name + '</span>'
                        + '<span class="ptb-card-sub">' + s.desc + '</span>'
                        + '</div>';
                });
                html += '</div>';
            }

            else if (state.step === 1) {
                html += '<p class="ptb-question">How big is your design?</p><div class="ptb-grid-sizes">';
                SIZES.forEach(function(s) {
                    var active = state.size === s.name ? ' ptb-card--active' : '';
                    html += '<div class="ptb-card' + active + '" onclick="ptbPick(\'size\',\'' + s.name.replace(/'/g, "\\'") + '\',2)" role="button" tabindex="0">'
                        + '<span class="ptb-card-name">' + s.name + '</span>'
                        + '<span class="ptb-card-sub">' + s.sub + '</span>'
                        + '</div>';
                });
                html += '</div>';
            }

            else if (state.step === 2) {
                html += '<p class="ptb-question">Where on your body?</p><div class="ptb-grid-places">';
                PLACEMENTS.forEach(function(p) {
                    var active = state.placement === p.name ? ' ptb-card--active' : '';
                    html += '<div class="ptb-card ptb-card--compact' + active + '" onclick="ptbPick(\'placement\',\'' + p.name.replace(/'/g, "\\'") + '\',3)" role="button" tabindex="0">'
                        + '<span class="ptb-card-name">' + p.name + '</span>'
                        + '</div>';
                });
                html += '</div>';
            }

            else if (state.step === 3) {
                html += '<p class="ptb-question">How detailed is the design?</p><div class="ptb-grid-complex">';
                COMPLEXITY.forEach(function(c) {
                    var active = state.complexity === c.label ? ' ptb-card--active' : '';
                    html += '<div class="ptb-card ptb-card--horizontal' + active + '" onclick="ptbPickFinal(\'' + c.label.replace(/'/g, "\\'") + '\')" role="button" tabindex="0">'
                        + '<span class="ptb-card-name">' + c.label + '</span>'
                        + '<span class="ptb-card-sub">' + c.sub + '</span>'
                        + '</div>';
                });
                html += '</div>';
            }

            html += '</div>'; // .ptb-step
        }

        /* ---- RESULT ---- */
        if (state.showResult && state.style && state.size && state.placement && state.complexity) {
            var range = calcRange();
            var waMsg = 'Hi Primitive Tattoo Bali! I used the price estimator.\n\n'
                + 'Style: ' + state.style + '\n'
                + 'Size: ' + state.size + '\n'
                + 'Placement: ' + state.placement + '\n'
                + 'Complexity: ' + state.complexity + '\n\n'
                + 'Estimated range: IDR ' + fmtIDR(range[0]) + ' – ' + fmtIDR(range[1]) + '\n\n'
                + 'Can I get an exact quote?';

            html += '<div class="ptb-result">'
                + '<p class="ptb-result-eyebrow">Your Estimate</p>'
                + '<div class="ptb-pills">';

            [state.style, state.size, state.placement, state.complexity].forEach(function(v) {
                html += '<span class="ptb-pill">' + v + '</span>';
            });

            html += '</div>'
                + '<div class="ptb-result-price">IDR ' + fmtIDR(range[0]) + ' – ' + fmtIDR(range[1]) + '</div>'
                + '<p class="ptb-result-disclaimer">Estimate only · Final price confirmed at consultation</p>'
                + '<a class="ptb-btn-gold" href="' + waLink(waMsg) + '" target="_blank" rel="noopener">Get Exact Quote on WhatsApp</a><br>'
                + '<button class="ptb-btn-ghost" onclick="ptbReset()">Start over</button>'
                + '</div>';
        }

        root.innerHTML = html;
    }

    /* ============================================================
       GLOBAL ACTIONS (called from inline onclick)
    ============================================================ */
    window.ptbPick = function(field, value, nextStep) {
        state[field] = value;
        setTimeout(function() { state.step = nextStep; render(); }, 200);
    };

    window.ptbPickFinal = function(value) {
        state.complexity = value;
        setTimeout(function() { state.showResult = true; render(); }, 350);
    };

    window.ptbGoStep = function(i) {
        state.step = i;
        state.showResult = false;
        render();
    };

    window.ptbSelectPromo = function() {
        state.promoMode = true;
        render();
    };

    window.ptbBackToEstimator = function() {
        state.promoMode = false;
        render();
    };

    window.ptbReset = function() {
        state = { step: 0, style: null, size: null, placement: null, complexity: null, showResult: false, promoMode: false };
        render();
    };

    /* ============================================================
       INIT — run when DOM is ready
    ============================================================ */
    function init() {
        var root = document.getElementById('ptb-estimator-root');
        if (root) render();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
