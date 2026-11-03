(function () {
  'use strict';

  function showAtomsMenu() {
    var menu = document.getElementById('toplevel_page_as-atomic-content');
    var sidebar = document.getElementById('adminmenuwrap');

    if (!menu || !sidebar) {
      return;
    }

    menu.classList.add('wp-has-current-submenu', 'wp-menu-open');
    menu.classList.remove('wp-not-current-submenu');

    var menuLink = menu.querySelector('a.menu-top');
    if (menuLink) {
      menuLink.setAttribute('aria-expanded', 'true');
    }

    if (!window.matchMedia('(min-width: 48.9375rem)').matches) {
      return;
    }

    var menuTop = menu.getBoundingClientRect().top;
    var sidebarTop = sidebar.getBoundingClientRect().top;
    sidebar.scrollTop += menuTop - sidebarTop - 16;
  }

  window.addEventListener('load', showAtomsMenu);
}());
