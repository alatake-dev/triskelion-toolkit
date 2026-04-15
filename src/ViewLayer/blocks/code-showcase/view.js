document.addEventListener('DOMContentLoaded', () => {
    /**
     * i18n support
     * Intentamos obtener la función de traducción de WordPress.
     * Si no está disponible, usamos un fallback que regresa el texto original.
     */
    const { __ } = wp.i18n || { __: ( s ) => s };

    const containers = document.querySelectorAll('.tsk-code-showcase-container');

    containers.forEach(container => {
        const tabs = container.querySelectorAll('.tsk-tab');
        const panes = container.querySelectorAll('.tsk-code-pane');
        const copyBtn = container.querySelector('.tsk-copy-button');
        const fileSelect = container.querySelector('.tsk-file-select');

        /**
         * Función central de cambio de estado
         * @param {number} index - El índice del archivo a mostrar
         */
        const switchToFile = (index) => {
            // 1. Limpiar estados previos
            tabs.forEach(t => {
                t.classList.remove('active');
                t.setAttribute('aria-selected', 'false');
                t.setAttribute('tabindex', '-1');
            });
            panes.forEach(p => {
                p.classList.remove('active');
                p.setAttribute('hidden', 'true');
            });

            // 2. Activar nuevos elementos
            if (tabs[index]) {
                tabs[index].classList.add('active');
                tabs[index].setAttribute('aria-selected', 'true');
                tabs[index].setAttribute('tabindex', '0');
            }
            if (panes[index]) {
                panes[index].classList.add('active');
                panes[index].removeAttribute('hidden');
            }

            // 3. Sincronizar el Selector Móvil
            if (fileSelect) fileSelect.value = index;

            /**
             * 4. Re-Highlight quirúrgico con Prism
             * Importante: Al cambiar de pestaña, Prism necesita volver a procesar
             * el bloque que ahora es visible.
             */
            if (window.Prism) {
                const code = panes[index].querySelector('code');
                if (code) {
                    window.Prism.highlightElement(code);
                }
            }
        };

        // Eventos para Desktop (Pestañas)
        tabs.forEach((tab, index) => {
            tab.addEventListener('click', () => switchToFile(index));
        });

        // Evento para Mobile (Selector)
        if (fileSelect) {
            fileSelect.addEventListener('change', (e) => {
                const index = parseInt(e.target.value, 10);
                switchToFile(index);
            });
        }

        // Lógica de Copiado al Portapapeles
        if (copyBtn) {
            copyBtn.addEventListener('click', () => {
                const activePane = container.querySelector('.tsk-code-pane.active code');
                if (!activePane) return;

                navigator.clipboard.writeText(activePane.innerText).then(() => {
                    const originalContent = copyBtn.innerHTML;
                    copyBtn.classList.add('copy-success');

                    // Checkmark SVG para feedback visual
                    copyBtn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';

                    setTimeout(() => {
                        copyBtn.classList.remove('copy-success');
                        copyBtn.innerHTML = originalContent;
                    }, 2000);
                });
            });
        }

        /**
         * Disparo inicial de Prism
         * Forzamos el resaltado de todos los bloques en el contenedor
         * para asegurar que los panes ocultos también se procesen.
         */
        if (window.Prism) {
            const allCodeBlocks = container.querySelectorAll('pre code');
            allCodeBlocks.forEach(code => {
                window.Prism.highlightElement(code);
            });
        }
    });
});