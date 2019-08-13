const config = require('next/config').default();

const defaultFalconConfig = {
  HTTP_AUTH_USER: '',
  HTTP_AUTH_PASS: '',
  APPLICATION_NAME: 'falcon_default',
  BACKEND_URL: 'http://falcon.docker.localhost',
  FRONTEND_URL: 'http://app.docker.localhost',
  CONSUMER_ID: '',
  ENVIRONMENT: 'development',
  PAYMENT_SECRET_HEADER_NAME: '',
  APP_ONLY_ROUTES: '',
  FAVICON: '',
  CLEAR_CACHE_URL: '/_clear-cache',
};

let APPLICATION_NAME;
let BACKEND_URL;
let FRONTEND_URL;
let CONSUMER_ID;
let ENVIRONMENT;
let PAYMENT_SECRET_HEADER_NAME;
let APP_ONLY_ROUTES;

if (config && config.publicRuntimeConfig) {
  ({
    APPLICATION_NAME,
    BACKEND_URL,
    FRONTEND_URL,
    CONSUMER_ID,
    ENVIRONMENT,
    PAYMENT_SECRET_HEADER_NAME,
    APP_ONLY_ROUTES,
  } = config.publicRuntimeConfig);
}


module.exports = {
  defaultFalconConfig,
  APPLICATION_NAME,
  BACKEND_URL,
  FRONTEND_URL,
  CONSUMER_ID,
  ENVIRONMENT,
  PAYMENT_SECRET_HEADER_NAME,
  APP_ONLY_ROUTES,
};
