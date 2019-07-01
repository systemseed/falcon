const withPlugins = require('next-compose-plugins');
const withTranspileModules = require('next-transpile-modules');

const nextConfig = {
  webpack: (config) => {
    // Fixes npm packages that depend on `fs` module.
    config.node = {
      fs: 'empty',
    };
    return config;
  },
};

module.exports = withPlugins([
  [withTranspileModules, {
    transpileModules: ['@systemseed/falcon'],
  }],
], nextConfig);
