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
                // Buscamos el bloque de código que está visible actualmente
                const activeCode = container.querySelector('.tsk-code-showcase__pane.is-active code');

                if (activeCode) {
                    // Usamos la API nativa de Clipboard (moderna y segura)
                    navigator.clipboard.writeText(activeCode.innerText).then(() => {
                        copyBtn.classList.add('is-success');

                        // Feedback visual temporal
                        setTimeout(() => {
                            copyBtn.classList.remove('is-success');
                        }, 2000);
                    }).catch(err => {
                        console.error('Error al copiar: ', err);
                    });
                }
            });
        }
    });
});