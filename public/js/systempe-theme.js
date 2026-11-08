/*
 * SystemPe - Selector de tema claro / oscuro
 * -------------------------------------------------------------------------
 * - Se ejecuta en <head> para aplicar el tema antes del primer pintado.
 * - Guarda la preferencia en localStorage ("systempe-theme").
 * - Inserta un boton con icono de luna/sol en la cabecera del sistema.
 *   Si la pagina no tiene cabecera (login, POS a pantalla completa),
 *   agrega un boton flotante en la esquina superior derecha.
 * -------------------------------------------------------------------------
 */
(function () {
    'use strict';

    var STORAGE_KEY = 'systempe-theme';
    var root = document.documentElement;

    function readStored() {
        try {
            return window.localStorage.getItem(STORAGE_KEY);
        } catch (e) {
            return null;
        }
    }

    function currentTheme() {
        return root.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    }

    function apply(theme, persist) {
        if (theme === 'dark') {
            root.setAttribute('data-theme', 'dark');
            root.classList.add('sp-dark');
        } else {
            root.removeAttribute('data-theme');
            root.classList.remove('sp-dark');
        }

        if (persist) {
            try {
                window.localStorage.setItem(STORAGE_KEY, theme);
            } catch (e) {
                /* almacenamiento no disponible */
            }
        }

        var btn = document.getElementById('sp-theme-toggle');
        if (btn) {
            btn.setAttribute('aria-pressed', theme === 'dark' ? 'true' : 'false');
            btn.setAttribute('title', theme === 'dark' ? 'Cambiar a modo claro' : 'Cambiar a modo oscuro');
            var icon = btn.querySelector('i');
            if (icon) {
                icon.className = theme === 'dark' ? 'fas fa-sun' : 'fas fa-moon';
            }
        }
    }

    /* Tema guardado, o el del sistema operativo la primera vez. */
    (function initTheme() {
        var stored = readStored();
        if (stored === 'dark' || stored === 'light') {
            apply(stored, false);
            return;
        }
        var prefersDark = false;
        try {
            prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        } catch (e) {
            prefersDark = false;
        }
        apply(prefersDark ? 'dark' : 'light', false);
    })();

    function createButton() {
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.id = 'sp-theme-toggle';
        btn.className = 'sp-theme-toggle';
        btn.setAttribute('aria-label', 'Cambiar entre modo claro y oscuro');
        btn.innerHTML = '<i class="fas fa-moon"></i>';
        btn.addEventListener('click', function () {
            apply(currentTheme() === 'dark' ? 'light' : 'dark', true);
        });
        return btn;
    }

    function mount() {
        if (document.getElementById('sp-theme-toggle')) {
            apply(currentTheme(), false);
            return;
        }

        var headerRight = document.querySelector('.header .header-right');
        var btn = createButton();

        if (headerRight) {
            var userbox = document.getElementById('userbox');
            btn.classList.add('sp-theme-toggle--header');
            if (userbox && userbox.parentNode === headerRight) {
                headerRight.insertBefore(btn, userbox);
            } else {
                headerRight.appendChild(btn);
            }
        } else {
            btn.classList.add('sp-theme-toggle--floating');
            document.body.appendChild(btn);
        }

        apply(currentTheme(), false);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mount);
    } else {
        mount();
    }
})();