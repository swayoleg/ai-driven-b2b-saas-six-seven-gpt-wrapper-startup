/* Alpine components + the two jQuery flourishes. No build step: this file is
   plain ES5-ish script, safe to drop into a Laravel Blade layout as-is. */

/* ---------- jQuery: the "live" telemetry that is not live ---------- */
jQuery(function ($) {
  function drift($el) {
    var base = parseInt($el.data('base'), 10);
    var swing = parseInt($el.data('swing'), 10) || 6;
    var cur = base;
    setInterval(function () {
      cur += Math.floor(Math.random() * swing) - Math.floor(swing / 3);
      $el.text(cur.toLocaleString('en-US'));
    }, 1400 + Math.random() * 900);
  }
  $('[data-counter]').each(function () { drift($(this)); });

  /* Buzzword generator — three columns, one straight face. */
  var A = ['agentic','composable','outcome-native','post-dashboard','zero-prompt','sovereign','headless','vertical','self-healing','deterministic'];
  var B = ['orchestration','substrate','telemetry','governance','enablement','abstraction','fabric','reconciliation','observability','alignment'];
  var C = ['layer for the enterprise','mesh','pipeline (managed)','loop','plane','for regulated industries','as a discipline','operating model'];
  function pick(a) { return a[Math.floor(Math.random() * a.length)]; }
  $(document).on('click', '[data-buzz-btn]', function () {
    var $out = $('[data-buzz-out]');
    if ($out.data('buzz-a')) { A = $out.data('buzz-a'); B = $out.data('buzz-b'); C = $out.data('buzz-c'); }
    $out.css('opacity', 0.25);
    setTimeout(function () {
      $out.text(pick(A) + ' ' + pick(B) + ' ' + pick(C)).css('opacity', 1);
    }, 180);
  });
});

/* ---------- Alpine ---------- */
document.addEventListener('alpine:init', function () {

  /* Waitlist wizard: five steps, one queue position, no queue. */
  Alpine.data('waitlist', function () {
    return {
      step: 1,
      total: 5,
      done: false,
      position: 0,
      locale: 'en-US',
      form: { email: '', company: '', size: '', urgency: 'Yesterday', maturity: '', pain: '', budget: 'Undisclosed' },
      get pct() { return Math.round((this.step - 1) / this.total * 100); },
      get canNext() {
        if (this.step === 1) return /.+@.+\..+/.test(this.form.email);
        if (this.step === 2) return this.form.company.length > 1 && this.form.size !== '';
        if (this.step === 3) return this.form.maturity !== '';
        if (this.step === 4) return this.form.pain.length > 3;
        return true;
      },
      next() { if (this.canNext && this.step < this.total) this.step++; },
      back() { if (this.step > 1) this.step--; },
      submit() {
        /* The position is a hash of the email. It is stable, which makes it
           feel real, and meaningless, which makes it honest. */
        var h = 0, s = this.form.email.toLowerCase();
        for (var i = 0; i < s.length; i++) { h = (h * 31 + s.charCodeAt(i)) % 100000; }
        this.position = 6700 + (h % 4200);
        this.locale = document.documentElement.lang === 'uk' ? 'uk-UA' : 'en-US';
        this.done = true;
      },
      moveUp() { if (this.position > 67) this.position -= 1; }
    };
  });

  /* Pricing: monthly, yearly, and the third option. */
  Alpine.data('pricing', function () {
    return {
      cycle: 'monthly',
      label: { monthly: '/ mo', yearly: '/ yr', undisclosed: '' },
      undisclosedLabel: 'Let\u2019s talk',
      init() {
        var d = this.$el.dataset;
        if (d.labelMonthly) this.label.monthly = d.labelMonthly;
        if (d.labelYearly) this.label.yearly = d.labelYearly;
        if (d.undisclosed) this.undisclosedLabel = d.undisclosed;
      },
      price(m) {
        if (this.cycle === 'monthly') return '$' + m.toLocaleString('en-US');
        if (this.cycle === 'yearly') return '$' + (m * 10.4).toLocaleString('en-US', { maximumFractionDigits: 0 });
        return this.undisclosedLabel;
      }
    };
  });

  /* Enterprise quote calculator. The maths are real. The units are not. */
  Alpine.data('quote', function () {
    return {
      seats: 120,
      agents: 40,
      regions: 2,
      readiness: 3,
      soc: true,
      insight: false,
      get base() { return this.seats * 41 + this.agents * 67; },
      get multiplier() { return (1 + this.regions * 0.34) * (1 + (5 - this.readiness) * 0.19); },
      get total() {
        var t = this.base * this.multiplier;
        if (this.soc) t += 6700;
        if (this.insight) t -= 670;
        return Math.round(t / 67) * 67;
      },
      get score() {
        var s = Math.min(6.7, (this.readiness * 1.1 + (this.soc ? 0.9 : 0) + (this.regions * 0.2)));
        return s.toFixed(1);
      },
      fmt(n) { return '$' + n.toLocaleString('en-US'); }
    };
  });

  /* Wallet copy buttons on the support page. */
  Alpine.data('wallets', function () {
    return {
      copied: '',
      copy(addr) {
        var self = this;
        navigator.clipboard.writeText(addr).then(function () {
          self.copied = addr;
          setTimeout(function () { if (self.copied === addr) self.copied = ''; }, 2000);
        });
      }
    };
  });
});

/* ---------- scroll reveal ---------- */
(function () {
  function start() {
    var els = document.querySelectorAll('[data-reveal]');
    if (!('IntersectionObserver' in window)) {
      for (var i = 0; i < els.length; i++) els[i].classList.add('is-in');
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (!e.isIntersecting) return;
        var d = parseInt(e.target.getAttribute('data-reveal-delay') || '0', 10);
        setTimeout(function () { e.target.classList.add('is-in'); }, d);
        io.unobserve(e.target);
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
    els.forEach(function (el) { io.observe(el); });
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
  else start();
})();
