/**
 * =============================================================================
 *  Portfolio SKS — Diaporama d'accueil : photos qui alternent (fondu/glissade)
 * =============================================================================
 *  - Rotation automatique (durée par photo : data-speed).
 *  - Transition fondu ou glissement (data-transition, pilotée par l'admin).
 *  - Flèches et points de navigation.
 *  - Pause au survol et pendant le toucher ; balayage tactile (mobile).
 *  - Chargement différé des photos suivantes (loading="lazy" côté HTML).
 *  - prefers-reduced-motion : AUCUNE rotation automatique (navigation
 *    manuelle uniquement) — conformément à la demande.
 *  - Click-to-load pour la vidéo (iframe créée uniquement au clic).
 * =============================================================================
 */
(function () {
  'use strict';

  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  document.querySelectorAll('.banner[data-banner]').forEach(function (banner) {
    var stage = banner.querySelector('.banner-stage');
    var slides = banner.querySelectorAll('.banner-slide');
    if (!stage || slides.length < 1) return;
    if (slides.length < 2) return; // une seule photo : affichage simple

    var dots = banner.querySelectorAll('.banner-dot');
    var idx = 0;
    var timer = null;
    var seconds = parseInt(banner.getAttribute('data-speed') || '6', 10) || 6;
    if (seconds < 2) seconds = 2;

    function show(next) {
      slides[idx].classList.remove('is-active');
      slides[idx].setAttribute('aria-hidden', 'true');
      dots[idx] && dots[idx].classList.remove('is-active');
      idx = (next + slides.length) % slides.length;
      slides[idx].classList.add('is-active');
      slides[idx].removeAttribute('aria-hidden');
      dots[idx] && dots[idx].classList.add('is-active');
      // Précharge la suivante juste après la transition.
      var nxt = slides[(idx + 1) % slides.length];
      var img = nxt && nxt.querySelector('img');
      if (img && img.getAttribute('loading') === 'lazy') {
        img.setAttribute('loading', 'eager');
      }
    }

    function play() {
      if (reduced || timer) return; // pas de rotation auto si reduced-motion
      timer = setInterval(function () { show(idx + 1); }, seconds * 1000);
    }

    function stop() {
      if (timer) {
        clearInterval(timer);
        timer = null;
      }
    }

    // Pause au survol / pendant le toucher.
    stage.addEventListener('mouseenter', stop);
    stage.addEventListener('mouseleave', play);
    stage.addEventListener('touchstart', stop, { passive: true });

    // Balayage tactile.
    var x0 = null;
    stage.addEventListener('touchstart', function (ev) {
      x0 = ev.touches[0].clientX;
    }, { passive: true });
    stage.addEventListener('touchend', function (ev) {
      if (x0 !== null) {
        var dx = ev.changedTouches[0].clientX - x0;
        x0 = null;
        if (Math.abs(dx) > 40) {
          show(idx + (dx < 0 ? 1 : -1));
        }
      }
      setTimeout(play, 4000); // reprend après une pause tactile
    }, { passive: true });

    // Flèches.
    var prev = banner.querySelector('.banner-prev');
    var next = banner.querySelector('.banner-next');
    if (prev) prev.addEventListener('click', function () { show(idx - 1); stop(); });
    if (next) next.addEventListener('click', function () { show(idx + 1); stop(); });

    // Points.
    dots.forEach(function (dot, i) {
      dot.addEventListener('click', function () { show(i); stop(); });
    });

    // Clavier.
    stage.addEventListener('keydown', function (ev) {
      if (ev.key === 'ArrowRight') { show(idx + 1); stop(); }
      if (ev.key === 'ArrowLeft') { show(idx - 1); stop(); }
    });

    play();
  });

  // Click-to-load : l'iframe (YouTube/Vimeo) n'est créée QU'AU CLIC.
  document.querySelectorAll('.c2l-facade').forEach(function (facade) {
    var playBtn = facade.querySelector('.c2l-play');
    if (!playBtn) return;
    playBtn.addEventListener('click', function () {
      var src = facade.getAttribute('data-c2l-src') || '';
      if (!src) return;
      var iframe = document.createElement('iframe');
      iframe.src = src + (src.indexOf('?') === -1 ? '?' : '&') + 'autoplay=1';
      iframe.title = facade.getAttribute('data-c2l-title') || '';
      iframe.setAttribute('allow', 'autoplay; encrypted-media; picture-in-picture; fullscreen');
      iframe.allowFullscreen = true;
      iframe.style.border = '0';
      iframe.style.width = '100%';
      iframe.style.height = '100%';
      facade.parentElement.innerHTML = '';
      facade.parentElement.appendChild(iframe);
    });
  });
})();
