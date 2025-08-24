const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .setOutputPath('public/build/')
    .setPublicPath('/build')
    
    // Point d'entrée principal (importe le CSS)
    .addEntry('app', './assets/app.js')
    
    // ⚠️ PAS de .addStyleEntry() séparé, ça crée des conflits
    
    // Activation de PostCSS (obligatoire pour Tailwind)
    .enablePostCssLoader()
    
    .splitEntryChunks()
    .enableStimulusBridge('./assets/controllers.json')
    .enableSingleRuntimeChunk()
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    .copyFiles({ from: './assets/images', to: 'images/[path][name].[hash:8].[ext]' })
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.38';
    })
;

module.exports = Encore.getWebpackConfig();