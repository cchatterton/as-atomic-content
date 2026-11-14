(function () {
  'use strict';

  document.addEventListener('click', function (event) {
    if (new URLSearchParams(window.location.search).get('p') !== '/pattern' ||
      event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ||
      !(event.target instanceof Element) ||
      !event.target.closest('.edit-site-sidebar-navigation-screen__title-icon .edit-site-sidebar-button')) {
      return;
    }

    event.preventDefault();
    event.stopImmediatePropagation();
    window.location.assign(new URL('index.php', window.location.href).href);
  }, true);
}());
