/**
 * Triskelion Toolkit - Admin Inventory Engine & Live Preview
 * Estándar 2026: Vanilla JS + Delegación de Eventos.
 */
(() => {
    const inventory = window.tskInventoryData?.allLanguages || [];

    const init = () => {
        const input = document.getElementById('tsk-lang-finder');
        const resultsList = document.getElementById('tsk-search-results');
        const pillsContainer = document.getElementById('tsk-active-langs');

        const themeSelect = document.querySelector('select[name="tsk_showcase_settings[active_theme]"]');
        const previewBox = document.querySelector('.tsk-code-showcase-preview');

        if (!input || !resultsList || !pillsContainer) return;

        /**
         * Lógica de Búsqueda Reactiva
         */
        input.addEventListener('input', (e) => {
            const query = e.target.value.toLowerCase().trim();
            resultsList.innerHTML = '';

            if (query.length < 1) {
                resultsList.hidden = true;
                return;
            }

            const matches = inventory.filter(lang =>
                lang.includes(query) &&
                !pillsContainer.querySelector(`input[value="${lang}"]`)
            );

            matches.forEach(lang => {
                const li = document.createElement('li');
                li.className = 'tsk-results-list__item';
                li.textContent = lang.toUpperCase();

                li.addEventListener('click', (event) => {
                    event.preventDefault();
                    createLanguagePill(lang, pillsContainer);
                    input.value = '';
                    resultsList.hidden = true;
                    input.focus();
                });

                resultsList.appendChild(li);
            });

            resultsList.hidden = matches.length === 0;
        });

        /**
         * Delegación de Eventos para eliminar Pills
         */
        pillsContainer.addEventListener('click', (e) => {
            const btn = e.target.closest('.tsk-pill__remove');
            if (btn) {
                e.preventDefault();
                btn.closest('.tsk-pill').remove();
            }
        });

        /**
         * LIVE PREVIEW: Cambio de tema en tiempo real
         * Solo actúa si existen los elementos en el DOM de la página de ajustes.
         */
        if (themeSelect && previewBox) {
            themeSelect.addEventListener('change', function() {
                const classes = previewBox.className.split(" ").filter(c => !c.startsWith('is-theme-'));
                previewBox.className = classes.join(" ");
                previewBox.classList.add(`is-theme-${this.value}`);
            });
        }

        // Cerrar resultados al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (!input.contains(e.target) && !resultsList.contains(e.target)) {
                resultsList.hidden = true;
            }
        });
    };

    /**
     * Función constructora de Pills
     */
    const createLanguagePill = (lang, container) => {
        const pill = document.createElement('div');
        pill.className = 'tsk-pill';
        pill.innerHTML = `
            <span class="tsk-pill__label">${lang.toUpperCase()}</span>
            <input type="hidden" name="tsk_showcase_settings[active_languages][]" value="${lang}">
            <button type="button" class="tsk-pill__remove" aria-label="Eliminar">&times;</button>
        `;
        container.appendChild(pill);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();