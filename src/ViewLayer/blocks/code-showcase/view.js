document.addEventListener('DOMContentLoaded', () => {
    const containers = document.querySelectorAll('.tsk-code-showcase-container');

    containers.forEach(container => {
        const tabs = container.querySelectorAll('.tsk-tab');
        const panes = container.querySelectorAll('.tsk-code-pane');
        const copyBtn = container.querySelector('.tsk-copy-button');

        // 1. Cambio de Tabs e Integración con Prism
        tabs.forEach((tab, index) => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                panes.forEach(p => p.classList.remove('active'));

                tab.classList.add('active');
                panes[index].classList.add('active');

                // 💡 RE-HIGHLIGHT
                if (typeof Prism !== 'undefined') {
                    const activeCode = panes[index].querySelector('code');
                    // Forzamos el resaltado. El Autoloader se encargará de pedir el JS si no existe.
                    Prism.highlightElement(activeCode);
                }
            });
        });

        // 2. Copiado (Tu lógica actual que ya funciona)
        if (copyBtn) {
            // ... (mantén tu código de copyBtn igual) ...
        }

        // 🚀 TRUCO DE SENIOR: Forzar la carga de TODOS los lenguajes presentes
        // Aunque estén ocultos, esto dispara las peticiones al Network de una vez.
        if (typeof Prism !== 'undefined') {
            const allCodeBlocks = container.querySelectorAll('pre code');
            allCodeBlocks.forEach(code => Prism.highlightElement(code));
        }
    });
});