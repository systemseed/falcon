const withPlugins = require('next-compose-plugins');
const withTranspileModules = require('next-transpile-modules');
const withSass = require('@zeit/next-sass');

const nextConfig = {
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
};

module.exports = withPlugins([
  [withTranspileModules, {
    transpileModules: ['@systemseed/falcon'],
  }],
], withSass(nextConfig));
