/* Alpine components plus two small flourishes. No build step beyond gulp
   concatenating and minifying this file; it is plain ES5-ish script.

   These two effects used to be the only reason the site loaded jQuery — 88 KB
   to drift some fake numbers and shuffle three word lists. They are ~30 lines
   of DOM API, so jQuery went. */

/* ---------- the "live" telemetry that is not live ---------- */
(function () {
  function onReady(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn);
    } else {
      fn();
    }
  }

  onReady(function () {
    Array.prototype.forEach.call(document.querySelectorAll('[data-counter]'), function (el) {
      var cur = parseInt(el.getAttribute('data-base'), 10);
      var swing = parseInt(el.getAttribute('data-swing'), 10) || 6;

      if (isNaN(cur)) return;

      setInterval(function () {
        cur += Math.floor(Math.random() * swing) - Math.floor(swing / 3);
        el.textContent = cur.toLocaleString('en-US');
      }, 1400 + Math.random() * 900);
    });
  });

  /* Buzzword generator — three columns, one straight face. */
  var A = ['agentic','composable','outcome-native','post-dashboard','zero-prompt','sovereign','headless','vertical','self-healing','deterministic'];
  var B = ['orchestration','substrate','telemetry','governance','enablement','abstraction','fabric','reconciliation','observability','alignment'];
  var C = ['layer for the enterprise','mesh','pipeline (managed)','loop','plane','for regulated industries','as a discipline','operating model'];

  function pick(a) { return a[Math.floor(Math.random() * a.length)]; }

  /* jQuery's .data() parsed JSON out of the attribute for us; the DOM API
     hands back a string, so the page's own word lists need parsing. */
  function listFrom(el, attr, fallback) {
    var raw = el.getAttribute(attr);

    if (!raw) return fallback;

    try {
      var parsed = JSON.parse(raw);

      return Array.isArray(parsed) && parsed.length ? parsed : fallback;
    } catch (error) {
      return fallback;
    }
  }

  document.addEventListener('click', function (event) {
    var target = event.target;

    if (!target || typeof target.closest !== 'function' || !target.closest('[data-buzz-btn]')) return;

    var out = document.querySelector('[data-buzz-out]');

    if (!out) return;

    var a = listFrom(out, 'data-buzz-a', A);
    var b = listFrom(out, 'data-buzz-b', B);
    var c = listFrom(out, 'data-buzz-c', C);

    out.style.opacity = '0.25';
    setTimeout(function () {
      out.textContent = pick(a) + ' ' + pick(b) + ' ' + pick(c);
      out.style.opacity = '1';
    }, 180);
  });
})();

/* ---------- form posting ---------- */
/* The two forms live inside page content stored in the database, so there is no
   Blade @csrf to lean on — the token comes from the <meta> tag in the layout. */
function postForm(url, data) {
  var meta = document.querySelector('meta[name="csrf-token"]');
  return fetch(url, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': meta ? meta.getAttribute('content') : ''
    },
    body: JSON.stringify(data)
  }).then(function (res) {
    return res.json().catch(function () { return {}; }).then(function (body) {
      if (!res.ok) { throw body; }
      return body;
    });
  });
}

/* ---------- Alpine ---------- */
document.addEventListener('alpine:init', function () {

  /* Waitlist wizard: five steps, one queue position, no queue. */
  Alpine.data('waitlist', function () {
    return {
      step: 1,
      total: 5,
      done: false,
      busy: false,
      error: '',
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
        if (this.busy) return;
        var self = this;
        this.busy = true;
        this.error = '';
        /* The position is a stable hash of the email, now issued by the server
           so the number stored matches the number shown. */
        postForm('/waitlist', this.form).then(function (body) {
          self.position = body.position;
          self.locale = document.documentElement.lang === 'uk' ? 'uk-UA' : 'en-US';
          self.done = true;
        }).catch(function (body) {
          self.error = (body && body.message) || 'Something went wrong. The queue is unmoved. Try again.';
        }).finally(function () { self.busy = false; });
      },
      moveUp() { if (this.position > 67) this.position -= 1; }
    };
  });

  /* Newsletter: subscribes for real, admits to nothing. */
  Alpine.data('newsletter', function () {
    return {
      email: '',
      sent: false,
      busy: false,
      error: '',
      submit() {
        if (this.busy) return;
        var self = this;
        this.busy = true;
        this.error = '';
        postForm('/newsletter', { email: this.email }).then(function () {
          self.sent = true;
        }).catch(function (body) {
          self.error = (body && body.message) || 'That did not go through. Try again.';
        }).finally(function () { self.busy = false; });
      }
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
