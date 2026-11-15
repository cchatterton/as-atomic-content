(function () {
  'use strict';

  var root = document.querySelector('.asac-help');
  if (!root || !window.ASAC_HELP) {
    return;
  }

  var article = root.querySelector('.asac-help-article');
  var outline = root.querySelector('.asac-help-outline');
  var status = root.querySelector('.asac-help-status');
  var links = Array.prototype.slice.call(root.querySelectorAll('.asac-help-topics a[data-guide]'));
  var activeRequest = null;
  if (!links.length) {
    return;
  }

  function guideLink(slug) {
    return links.find(function (link) { return link.dataset.guide === slug; });
  }

  function setActive(slug) {
    root.dataset.currentGuide = slug;
    links.forEach(function (link) {
      if (link.dataset.guide === slug) {
        link.setAttribute('aria-current', 'page');
      } else {
        link.removeAttribute('aria-current');
      }
    });
  }

  function buildOutline() {
    outline.replaceChildren();
    var headings = article.querySelectorAll('h2[id], h3[id]');
    if (!headings.length) {
      return;
    }

    var title = document.createElement('h2');
    title.textContent = 'On this page';
    var list = document.createElement('ul');
    headings.forEach(function (heading) {
      var item = document.createElement('li');
      if (heading.tagName === 'H3') {
        item.className = 'asac-help-subsection';
      }
      var link = document.createElement('a');
      link.href = '#' + encodeURIComponent(heading.id);
      link.textContent = heading.textContent;
      item.appendChild(link);
      list.appendChild(item);
    });
    outline.appendChild(title);
    outline.appendChild(list);
  }

  function scrollToSection(hash) {
    if (!hash) {
      return;
    }
    var id;
    try {
      id = decodeURIComponent(hash.slice(1));
    } catch (error) {
      return;
    }
    var section = document.getElementById(id);
    if (section && article.contains(section)) {
      section.scrollIntoView({ block: 'start' });
    }
  }

  function loadGuide(slug, url, push) {
    var link = guideLink(slug);
    if (!link) {
      return;
    }
    if (activeRequest) {
      activeRequest.abort();
    }
    activeRequest = new AbortController();
    article.setAttribute('aria-busy', 'true');
    status.textContent = 'Loading ' + link.textContent + '…';

    var body = new URLSearchParams({
      action: 'asac_help_guide',
      guide: slug,
      nonce: window.ASAC_HELP.nonce
    });
    fetch(window.ASAC_HELP.ajaxUrl, {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
      body: body.toString(),
      signal: activeRequest.signal
    }).then(function (response) {
      if (!response.ok) {
        throw new Error('Guide request failed');
      }
      return response.json();
    }).then(function (result) {
      if (!result.success || !result.data || result.data.slug !== slug) {
        throw new Error('Guide response was invalid');
      }
      article.innerHTML = result.data.html;
      article.removeAttribute('aria-busy');
      setActive(slug);
      buildOutline();
      if (push) {
        window.history.pushState({ guide: slug }, '', url.href);
      }
      scrollToSection(url.hash);
      if (!url.hash) {
        article.focus({ preventScroll: true });
        article.scrollIntoView({ block: 'start' });
      }
      status.textContent = result.data.title + ' loaded.';
    }).catch(function (error) {
      if (error.name === 'AbortError') {
        return;
      }
      article.removeAttribute('aria-busy');
      status.textContent = window.ASAC_HELP.error;
      // The server-rendered URL remains a usable fallback when AJAX is unavailable.
      window.location.assign(url.href);
    });
  }

  root.addEventListener('click', function (event) {
    var link = event.target instanceof Element ? event.target.closest('a[href]') : null;
    if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey || link.target) {
      return;
    }

    var url = new URL(link.href, window.location.href);
    if (url.origin !== window.location.origin || url.pathname !== window.location.pathname || url.searchParams.get('page') !== 'as-atomic-content-help') {
      return;
    }

    var slug = url.searchParams.get('guide');
    if (!guideLink(slug)) {
      return;
    }
    event.preventDefault();
    if (slug === root.dataset.currentGuide) {
      window.history.pushState({ guide: slug }, '', url.href);
      scrollToSection(url.hash);
      return;
    }
    loadGuide(slug, url, true);
  });

  window.addEventListener('popstate', function () {
    var url = new URL(window.location.href);
    var slug = url.searchParams.get('guide') || links[0].dataset.guide;
    if (slug === root.dataset.currentGuide) {
      scrollToSection(url.hash);
    } else {
      loadGuide(slug, url, false);
    }
  });

  buildOutline();
  scrollToSection(window.location.hash);
}());
