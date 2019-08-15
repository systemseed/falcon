const nextjs = require('next');
const express = require('express');
const favicon = require('serve-favicon');
const decoupledRouter = require('./decoupledRouter');
const frontendOnlyRoutes = require('./frontendOnlyRoutes');
const { globalSettingsForApp, handleHomepageRequest } = require('./globalSettings');
const clearCache = require('./clearCache');
const applyFalconRoutingConfigurations = require('./applyFalconRoutingConfigurations');
const { defaultFalconConfig } = require('../utils/constants');

const startFalconServer = (
  userFalconConfig = {},
  userNextConfig = {},
  expressServer = express(),
) => new Promise((resolve, reject) => {
  // Merge default falcon config with config from user.
  const falconConfig = Object.assign(defaultFalconConfig, userFalconConfig);
  const {
    HTTP_AUTH_USER,
    HTTP_AUTH_PASS,
    APPLICATION_NAME,
    BACKEND_URL,
    FRONTEND_URL,
    CONSUMER_ID,
    ENVIRONMENT,
    PAYMENT_SECRET_HEADER_NAME,
    APP_ONLY_ROUTES,
    FAVICON,
    CLEAR_CACHE_URL,
  } = falconConfig;

  const application = nextjs(userNextConfig);

  // Disable routes like /node/page.
  application.nextConfiguseFileSystemPublicRoutes = false;

  // Define variables for server side.
  application.nextConfig.serverRuntimeConfig = Object.assign(
    application.nextConfig.serverRuntimeConfig || {},
    { HTTP_AUTH_USER, HTTP_AUTH_PASS },
  );

  // Define variables for client side.
  application.nextConfig.publicRuntimeConfig = Object.assign(
    application.nextConfig.publicRuntimeConfig || {},
    {
      APPLICATION_NAME,
      BACKEND_URL,
      FRONTEND_URL,
      CONSUMER_ID,
      ENVIRONMENT,
      PAYMENT_SECRET_HEADER_NAME,
      APP_ONLY_ROUTES,
    },
  );

  application
    .prepare()
    .then(() => {
      const server = applyFalconRoutingConfigurations(application, expressServer, falconConfig);

      if (FAVICON) {
        server.use(favicon(`${application.dir}${FAVICON}`));
      }

      if (CLEAR_CACHE_URL) {
        server.use(CLEAR_CACHE_URL, clearCache);
      }

      server.use(globalSettingsForApp(application, APPLICATION_NAME));
      server.use(handleHomepageRequest);

      if (APP_ONLY_ROUTES) {
        server.use(frontendOnlyRoutes(application, APP_ONLY_ROUTES));
      }

      // Handle all other requests using our custom router which is a mix
      // or original Next.js logic and Drupal routing logic.
      server.get('*', (req, res) => decoupledRouter(req, res, application));

      resolve(server);
    })
    .catch(error => reject(error));
});

module.exports = startFalconServer;
