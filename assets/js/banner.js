/**
 * =============================================================================
 *  Portfolio SKS — Diaporama d'accueil (photos qui alternent, fondu)
 * =============================================================================
 *  - Rotation automatique toutes les --banner-speed secondes / slides.
 *  - Pause au survol (desktop) et pendant le toucher (mobile).
 *  - prefers-reduced-motion : aucune rotation automatique, navigation par
 *    balayage tactile natif (scroll-snap via CSS) et boutons précédent/suivant.
 * =============================================================================
 */
(function () {
  'use strict';

  var stages = document.querySelectorAll('.banner-stage');
  if (!stages.length) return;

  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  Array.prototype.forEach.call(stages, function (stage) {
    var slides = stage.querySelectorAll('.banner-slide');
    if (slides.length < 2) return;

    var idx = 0;
    var timer = null;
    var banner = stage.closest('.banner');
    var seconds = 5; // durée par slide par défaut
    if (banner) {
      var js = banner.querySelector('script[src*="banner.js"]');
      seconds = parseInt(banner.getAttribute('data-speed') || '5', 10) || 5;
      if (seconds < 2) seconds = 2;
    }

    function show(next) {
      slides[idx].classList.remove('is-active');
      slides[idx].setAttribute('aria-hidden', 'true');
      idx = (next + slides.length) % slides.length;
      slides[idx].classList.add('is-active');
      slides[idx].removeAttribute('aria-hidden');
    }

    function play() {
      if (reduced || timer) return;
      timer = setInterval(function () { show(idx + 1); }, seconds * 1000);
    }

    function stop() {
      if (timer) {
        clearInterval(timer);
        timer = null;
      }
    }

    // Pause au survol et pendant le toucher.
    stage.addEventListener('mouseenter', stop);
    stage.addEventListener('mouseleave', play);
    stage.addEventListener('touchstart', stop, { passive: true });
    stage.addEventListener('touchend', function () {
      // petit délai : évite de repartir pendant un balayage
      setTimeout(play, 2500);
    }, { passive: true });

    // Balayage tactile (même en mode animation, geste prioritaire).
    var x0 = null;
    stage.addEventListener('touchstart', function (ev) {
      x0 = ev.touches[0].clientX;
    }, { passive: true });
    stage.addEventListener('touchend', function (ev) {
      if (x0 === null) return;
      var dx = ev.changedTouches[0].clientX - x0;
      x0 = null;
      if (Math.abs(dx) > 40) {
        show(idx + (dx < 0 ? 1 : -1));
        stop();
        setTimeout(play, 5000);
      }
    }, { passive: true });

    // Clavier (accessibilité).
    stage.addEventListener('keydown', function (ev) {
      if (ev.key === 'ArrowRight') { show(idx + 1); stop(); }
      if (ev.key === 'ArrowLeft') { show(idx - 1); stop(); }
    });

    play();
  });
})();
