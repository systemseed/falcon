const routes = require('./routes');

module.exports = {
  APPLICATION_NAME: 'falcon_default', // Config page entity id.
  BACKEND_URL: process.env.BACKEND_URL,
  FRONTEND_URL: process.env.FRONTEND_URL,
  CONSUMER_ID: process.env.CONSUMER_ID,
  ENVIRONMENT: process.env.ENVIRONMENT,
  PAYMENT_SECRET_HEADER_NAME: process.env.PAYMENT_SECRET_HEADER_NAME,
  HTTP_AUTH_USER: process.env.HTTP_AUTH_USER,
  HTTP_AUTH_PASS: process.env.HTTP_AUTH_PASS,
  APP_ONLY_ROUTES: routes,
  FAVICON: '/static/favicon.ico',
  CLEAR_CACHE_URL: '/clear-cache',
  STATIC_CACHE_MAX_AGE: '7d',
};
