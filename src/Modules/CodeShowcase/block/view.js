/**
 * Triskelion Code Showcase - Frontend Interactivity
 * Versión 2026: Sincronización de Pestañas y Selectores Móviles.
 */
document.addEventListener('DOMContentLoaded', () => {
    const showcases = document.querySelectorAll('.tsk-code-showcase-container');

    showcases.forEach(container => {
        const tabs = container.querySelectorAll('.tsk-tab');
        const panes = container.querySelectorAll('.tsk-code-pane');
        const copyBtn = container.querySelector('.tsk-copy-button');
        const fileSelect = container.querySelector('.tsk-file-select'); // El que te faltaba

        /**
         * Función maestra de cambio de estado
         * @param {number} index - El índice del archivo a mostrar
         */
        const switchToFile = (index) => {
            tabs.forEach((t, i) => {
                const isActive = i === parseInt(index);
                t.classList.toggle('active', isActive);
                t.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            panes.forEach((p, i) => {
                const isActive = i === parseInt(index);
                p.classList.toggle('active', isActive);
                if (isActive) { p.removeAttribute('hidden'); }
                else { p.setAttribute('hidden', 'true'); }
            });

            if (fileSelect) {
                fileSelect.value = index;
            }
        };

        // Evento para Pestañas (Desktop)
        tabs.forEach((tab, index) => {
            tab.addEventListener('click', () => switchToFile(index));
        });

        // Evento para el Select (Móvil) - AQUÍ ESTABA EL FANTASMA
        if (fileSelect) {
            fileSelect.addEventListener('change', (e) => {
                switchToFile(e.target.value);
            });
        }

        // Lógica de Copiado (Copia solo el código visible)
        if (copyBtn) {
            copyBtn.addEventListener('click', () => {
                const activePane = container.querySelector('.tsk-code-pane.active code');
                if (!activePane) return;

                navigator.clipboard.writeText(activePane.innerText).then(() => {
                    copyBtn.classList.add('copy-success');
                    setTimeout(() => copyBtn.classList.remove('copy-success'), 2000);
                });
            });
        }
    });
});