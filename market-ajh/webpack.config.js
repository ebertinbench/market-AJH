const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
  Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
  .setOutputPath('public/build/')
  .setPublicPath('/build')
  .addEntry('app', './assets/app.js')
  .enableSingleRuntimeChunk()
  .splitEntryChunks()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .copyFiles({ from: './assets/images', to: 'images/[path][name].[hash:8].[ext]' })
;

module.exports = Encore.getWebpackConfig();