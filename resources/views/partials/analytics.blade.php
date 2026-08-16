@if($id = config('site.analytics_id'))
{{-- Google Analytics, loaded on first interaction rather than on page load.

     gtag.js is ~90KB of third-party JavaScript that the page does not need in
     order to be read. Deferring it until the visitor scrolls, taps, clicks or
     types keeps it off the critical path entirely.

     The trade-off: a visitor who opens the page and leaves without touching it
     is never counted. Sessions will read lower than a standard install, and
     bounce rate is not comparable to one. If that matters more than the load
     time, add a setTimeout fallback to load() below. --}}
<script>
(function () {
  var ID = @json($id);
  var EVENTS = ['scroll', 'pointerdown', 'keydown', 'touchstart'];
  var started = false;

  function load() {
    if (started) return;
    started = true;

    EVENTS.forEach(function (event) {
      window.removeEventListener(event, load, { passive: true });
    });

    window.dataLayer = window.dataLayer || [];
    window.gtag = function () { window.dataLayer.push(arguments); };
    window.gtag('js', new Date());
    window.gtag('config', ID);

    var tag = document.createElement('script');
    tag.async = true;
    tag.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(ID);
    document.head.appendChild(tag);
  }

  EVENTS.forEach(function (event) {
    window.addEventListener(event, load, { once: true, passive: true });
  });

  // Someone arriving on a deep link may already be scrolled before this runs.
  if (window.scrollY > 0) load();
})();
</script>
@endif
