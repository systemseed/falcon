const withTranspileModules = require('next-transpile-modules');
const withSass = require('@zeit/next-sass');

module.exports = withSass(withTranspileModules({
  // Tells webpack to treat node package @systemseed/falcon as
  // an uncompiled sources which need to be compiled.
  transpileModules: ['@systemseed/falcon'],
  webpack: (config) => {
    // Fixes npm packages that depend on `fs` module.
    config.node = { // eslint-disable-line no-param-reassign
      fs: 'empty',
    };
    return config;
  },
  // DON'T REMOVE RUNTIME CONFIGS.
  publicRuntimeConfig: {},
  serverRuntimeConfig: {},
}));
