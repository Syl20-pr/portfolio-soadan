/* ==========================================================================
   SKS Portfolio — minimal, purposeful JavaScript
   - Mobile navigation (accessible, keyboard friendly)
   - Sticky header state
   - Soft scroll reveal for sections
   - Contact form (FormSubmit, graceful file:// fallback)
   No decorative / generative effects.
   ========================================================================== */
(function () {
  'use strict';

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var isFr = document.documentElement.lang !== 'en';

  /* --------------------------------------- Bilingual CV download (FR / EN) */
  /*
   * Each page declares its language on <html lang="…">. The CV files live in
   * documents/ as CV_SKS.pdf (FR) and CV_SKS_EN.pdf (EN), one level up from
   * the /en/ pages. Every element carrying [data-cv] is synced with the
   * current language: file, visible label and accessible name.
   */
  var CV_LABEL = {
    fr: { text: 'Télécharger mon CV', aria: 'Télécharger mon CV au format PDF (français)' },
    en: { text: 'Download my CV', aria: 'Download my CV as a PDF (English)' }
  };

  function syncCvLinks() {
    var lang = isFr ? 'fr' : 'en';
    var file = isFr ? 'CV_SKS.pdf' : 'CV_SKS_EN.pdf';
    var inEnFolder = /\/en\//.test(window.location.pathname) || /\\en\\/.test(window.location.pathname);
    var href = (inEnFolder ? '../' : '') + 'documents/' + file;
    var copy = CV_LABEL[lang];

    Array.prototype.forEach.call(document.querySelectorAll('[data-cv]'), function (link) {
      link.setAttribute('href', href);
      link.setAttribute('download', file);
      link.setAttribute('aria-label', copy.aria);
      var textBox = link.querySelector('[data-cv-text]');
      if (textBox) textBox.textContent = copy.text;
    });

    Array.prototype.forEach.call(document.querySelectorAll('[data-cv-text]'), function (textBox) {
      if (!textBox.closest('[data-cv]')) textBox.textContent = copy.text;
    });
  }

  /* ------------------------------------------------------ Mobile navigation */
  var menuBtn = document.querySelector('.menu-btn');
  var nav = document.getElementById('site-nav');

  if (menuBtn && nav) {
    var closeNav = function () {
      nav.classList.remove('is-open');
      menuBtn.setAttribute('aria-expanded', 'false');
    };

    menuBtn.addEventListener('click', function () {
      var open = nav.classList.toggle('is-open');
      menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });

    /* Close once a destination has been chosen */
    nav.addEventListener('click', function (e) {
      if (e.target.closest('a')) closeNav();
    });

    /* Close when clicking outside the header */
    document.addEventListener('click', function (e) {
      if (!e.target.closest('.site-header')) closeNav();
    });

    /* Close on Escape */
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape') closeNav();
    });

    /* Reset state when returning to desktop width */
    window.addEventListener('resize', function () {
      if (window.innerWidth > 900) closeNav();
    });
  }

  /* --------------------------------------------------------- Sticky header */
  var header = document.querySelector('.site-header');

  if (header) {
    var onScroll = function () {
      header.classList.toggle('is-stuck', window.scrollY > 8);
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  /* ---------------------------------------------------- Soft scroll reveal */
  /*
   * Sections are visible by default. When one scrolls into view we play a short
   * rise-and-fade. The hidden state exists only inside the `reveal-in` keyframe
   * animation, which resolves to the visible state on its own, so a failure here
   * can never leave a section blank.
   */
  var revealTargets = document.querySelectorAll('[data-reveal]');

  if (revealTargets.length && !reduceMotion && 'IntersectionObserver' in window) {
    var observer = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('reveal');
          observer.unobserve(entry.target);
        }
      });
    }, { rootMargin: '0px 0px -6% 0px', threshold: 0.05 });

    Array.prototype.forEach.call(revealTargets, function (el) {
      observer.observe(el);
    });
  }

  /* --------------------------------------------------------- Contact form */
  /*
   * The form is now handled server-side (actions/contact.php): it stores the
   * message in MySQL and sends emails via SMTP. JavaScript only provides a
   * light, progressive enhancement — validating the fields and showing a
   * sending state. Without JavaScript, the native POST still works.
   */
  var contactForm = document.getElementById('contact-form');
  var formStatus = document.getElementById('form-status');

  if (contactForm && formStatus) {
    var isEn = document.documentElement.lang === 'en';

    var setStatus = function (text, color) {
      formStatus.hidden = false;
      formStatus.style.color = color || 'var(--muted)';
      formStatus.textContent = text;
    };

    contactForm.addEventListener('submit', function (e) {
      var submitBtn = contactForm.querySelector('button[type="submit"]');

      var fieldValue = function (fieldName) {
        var field = contactForm.querySelector('[name="' + fieldName + '"]');
        return field ? field.value : '';
      };

      var name = fieldValue('name').trim();
      var email = fieldValue('email').trim();
      var message = fieldValue('message').trim();

      /* Client-side check is a courtesy only: the server validates again. */
      if (name === '' || email === '' || message === '') {
        e.preventDefault();
        setStatus(
          isEn
            ? 'The form is incomplete. Please fill in all required fields.'
            : 'Le formulaire est incomplet. Merci de remplir tous les champs requis.',
          '#b3261e'
        );
        return;
      }

      /* Direct file:// viewing has no PHP backend — fall back to mailto. */
      if (window.location.protocol === 'file:') {
        e.preventDefault();
        var subject = fieldValue('subject') || (isEn ? 'New inquiry' : 'Nouvelle prise de contact');
        setStatus(
          isEn
            ? 'The contact service requires a web server. Opening your mail client instead…'
            : 'Le service de contact requiert un serveur web. Ouverture de votre messagerie…',
          'var(--navy)'
        );
        window.location.href = 'mailto:sylvainsoadan3@gmail.com'
          + '?subject=' + encodeURIComponent('[Portfolio] ' + subject)
          + '&body=' + encodeURIComponent('Name: ' + name + '\nEmail: ' + email + '\n\n' + message);
        return;
      }

      /* Real backend available: let the browser POST to actions/contact.php. */
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = isEn ? 'Sending…' : 'Envoi en cours…';
      }
      setStatus(isEn ? 'Sending your message…' : 'Envoi de votre message…', 'var(--muted)');
    });
  }

  /* ------------------------------------------- In-page section navigation */
  var jumpLinks = document.querySelectorAll('[data-jump]');

  if (jumpLinks.length) {
    jumpLinks.forEach(function (link) {
      link.addEventListener('click', function (e) {
        var id = link.getAttribute('href');
        if (!id || id.charAt(0) !== '#') return;
        var target = document.getElementById(id.slice(1));
        if (!target) return;
        e.preventDefault();
        target.scrollIntoView({
          behavior: reduceMotion ? 'auto' : 'smooth',
          block: 'start'
        });
        if (history.replaceState) history.replaceState(null, '', id);
      });
    });
  }

  /* --------------------------------- Same-page timeline reveal on scroll */
  var timelineStages = document.querySelectorAll('.timeline-stage');

  if (timelineStages.length && !reduceMotion && 'IntersectionObserver' in window) {
    var stageObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          stageObserver.unobserve(entry.target);
        }
      });
    }, { rootMargin: '0px 0px -10% 0px', threshold: 0.15 });

    timelineStages.forEach(function (stage) { stageObserver.observe(stage); });
  } else {
    timelineStages.forEach(function (stage) { stage.classList.add('is-visible'); });
  }

  /* ------------------------------------------------ Project domain filters */
  var filterBar = document.querySelector('[data-project-filters]');

  if (filterBar) {
    var projectCards = document.querySelectorAll('[data-domains]');
    var emptyNote = document.querySelector('[data-filter-empty]');

    filterBar.addEventListener('click', function (e) {
      var button = e.target.closest('button[data-filter]');
      if (!button) return;
      var wanted = button.getAttribute('data-filter');

      filterBar.querySelectorAll('button[data-filter]').forEach(function (b) {
        var on = b === button;
        b.classList.toggle('is-active', on);
        b.setAttribute('aria-pressed', on ? 'true' : 'false');
      });

      var shown = 0;
      projectCards.forEach(function (card) {
        var domains = (card.getAttribute('data-domains') || '').split(' ');
        var match = wanted === 'all' || domains.indexOf(wanted) !== -1;
        card.classList.toggle('is-hidden', !match);
        card.classList.toggle('is-filtered-in', match);
        if (match) shown += 1;
      });

      if (emptyNote) emptyNote.hidden = shown !== 0;
    });
  }

  /* -------------------------------------------- Expanding experience cards */
  var expanders = document.querySelectorAll('[data-expand]');

  expanders.forEach(function (trigger) {
    var panelId = trigger.getAttribute('aria-controls');
    var panel = panelId ? document.getElementById(panelId) : null;
    if (!panel) return;

    trigger.addEventListener('click', function () {
      var open = trigger.getAttribute('aria-expanded') === 'true';
      trigger.setAttribute('aria-expanded', open ? 'false' : 'true');
      panel.hidden = open;
    });
  });

  /* ---------------------------- International flag frame (page border) */
  /*
   * Four thin frise bands of small flag tiles frame the page. Each band is
   * decorative only (aria-hidden), pointer-events none, and hidden entirely
   * below 1024px so it never competes with content on tablets and phones.
   */
  var FLAG_CODES = [
    'tg', 'gh', 'ng', 'sn', 'ci', 'bj', 'ml', 'bf', 'ne', 'cm',
    'cd', 'ke', 'tz', 'rw', 'za', 'et', 'ma', 'dz', 'eg', 'tn',
    'fr', 'de', 'be', 'nl', 'lu', 'ch', 'it', 'es', 'pt', 'gb',
    'us', 'ca', 'mx', 'br', 'ar', 'cl', 'cn', 'jp', 'in', 'kr',
    'id', 'tr', 'ae', 'sa', 'qa', 'au', 'nz', 'sg', 'ph', 'th'
  ];

  function buildFlagBand(position) {
    var band = document.createElement('div');
    band.className = 'flag-frame flag-frame-' + position;
    band.setAttribute('aria-hidden', 'true');

    var order = FLAG_CODES.slice();
    if (position === 'top' || position === 'bottom') order.reverse();

    order.forEach(function (code, i) {
      var tile = document.createElement('span');
      tile.className = 'flag-tile';
      tile.style.setProperty('--i', String(i));
      tile.style.backgroundImage = 'url("https://flagcdn.com/' + code + '.svg")';
      band.appendChild(tile);
    });

    return band;
  }

  if (window.innerWidth >= 1024 && !document.body.classList.contains('is-printing')) {
    ['top', 'right', 'bottom', 'left'].forEach(function (position) {
      document.body.appendChild(buildFlagBand(position));
    });
  }

  /* ------------------------------------------------------------- Bootstraps */
  reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  syncCvLinks();
})();
