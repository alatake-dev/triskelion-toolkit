/**
 * Triskelion Code Showcase - Frontend Interactivity
 * Manejo de Tabs, Selector Móvil y Portapapeles.
 */
document.addEventListener('DOMContentLoaded', () => {
    // 1. Buscamos el bloque raíz con el nuevo nombre BEM
    const showcases = document.querySelectorAll('.tsk-code-showcase');

    showcases.forEach(container => {
        // 2. Mapeo de elementos usando la nueva nomenclatura __
        const tabs = container.querySelectorAll('.tsk-code-showcase__tab');
        const panes = container.querySelectorAll('.tsk-code-showcase__pane');
        const copyBtn = container.querySelector('.tsk-code-showcase__copy');
        const mobileSelect = container.querySelector('.tsk-code-showcase__select');

        /**
         * Función Maestra de Sincronización
         * Mantiene en espejo el estado de Tabs, Paneles y Select Móvil.
         */
        const updateVisibility = (index) => {
            const targetIndex = parseInt(index);

            tabs.forEach((tab, i) => {
                const isActive = i === targetIndex;
                tab.classList.toggle('is-active', isActive);
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            // Actualizar Paneles de Código (Accesibilidad WCAG 2.1)
            panes.forEach((pane, i) => {
                const isActive = i === targetIndex;
                pane.classList.toggle('is-active', isActive);
                if (isActive) {
                    pane.removeAttribute('hidden');
                } else {
                    pane.setAttribute('hidden', 'true');
                }
            });

            if (mobileSelect) {
                mobileSelect.value = targetIndex;
            }
        };

        // --- LISTENERS DE INTERACCIÓN ---
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const index = tab.getAttribute('data-index');
                updateVisibility(index);
            });
        });

        if (mobileSelect) {
            mobileSelect.addEventListener('change', (e) => {
                updateVisibility(e.target.value);
            });
        }

        if (copyBtn) {
            copyBtn.addEventListener('click', () => {
                const pane = document.querySelector('.tsk-code-showcase__pane.is-active code');
                const originalSVG = copyBtn.innerHTML;
                const checkSVG = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#27c93f" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>`;

                navigator.clipboard.writeText(pane.innerText).then(() => {
                    copyBtn.innerHTML = checkSVG;
                    copyBtn.classList.add('is-success');

                    setTimeout(() => {
                        copyBtn.innerHTML = originalSVG;
                        copyBtn.classList.remove('is-success');
                    }, 3000);
                });
            });
        }
    });
});