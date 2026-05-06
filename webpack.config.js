/**
 * Extensión de la configuración nativa de wp-scripts.
 * Objetivo: Compilar archivos SCSS independientes sin necesidad de un JS huérfano.
 */
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const path = require('path');

module.exports = {
    ...defaultConfig,
    entry: {
        ...defaultConfig.entry(),
        'admin-layout': path.resolve(process.cwd(), 'src/scss/admin-layout.scss'),
    },
    output: {
        ...defaultConfig.output,
        path: path.resolve(process.cwd(), 'build'),
    },
};