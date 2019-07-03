const withPlugins = require('next-compose-plugins');
const withTranspileModules = require('next-transpile-modules');
const nextRuntimeDotenv = require('next-runtime-dotenv');

// Makes certain variables accessible only at runtime visible for the application.
// Check if it supports withPlugins: https://github.com/tusbar/next-runtime-dotenv/issues/54
const withConfig = nextRuntimeDotenv({
  public: [
    'BACKEND_URL',
    'FRONTEND_URL',
    'CONSUMER_ID',
    'ENVIRONMENT',
    'PAYMENT_SECRET_HEADER_NAME',
  ],
  server: [
    'HTTP_AUTH_USER',
    'HTTP_AUTH_PASS',
  ],
});

const nextConfig = {
  webpack: (config) => {
    // Fixes npm packages that depend on `fs` module.
    config.node = { // eslint-disable-line no-param-reassign
      fs: 'empty',
    };
    return config;
  },
};

module.exports = withConfig(withPlugins([
  [withTranspileModules, {
    transpileModules: ['@systemseed/falcon'],
  }],
], nextConfig));
