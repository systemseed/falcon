const superAgent = require('superagent');
const superagentPrefix = require('superagent-prefix');
const nextConfig = require('next/config').default();

/**
 * This is universal (frontend & backend) Superagent initializer.
 * In most cases you will want to import '../utils/request` instead of this file.
 */
const initSuperagent = (config) => {
  if (config === undefined) {
    return undefined;
  }

  const {
    publicRuntimeConfig: { BACKEND_URL, CONSUMER_ID, PAYMENT_SECRET_HEADER_NAME },
    serverRuntimeConfig: { HTTP_AUTH_USER, HTTP_AUTH_PASS },
  } = config;

  const prefix = superagentPrefix(BACKEND_URL);

  // Get superagent object & make it ready to set some default values.
  const superagent = superAgent.agent()
    // Set the right URL prefix so that the request always
    // gets to the right place despite of being executed on
    // the server or client level.
    .use(prefix)
    .set('Accept', 'application/json')
    // We intentionally set consumer ID as a query parameter to avoid extra
    // OPTIONS check on common GET requests.
    .query({ consumerId: CONSUMER_ID });

  // If the current environment includes http auth variables, then include them
  // as a custom header into the request.
  if (HTTP_AUTH_USER && HTTP_AUTH_PASS) {
    superagent.set('HTTP-Auth', `${HTTP_AUTH_USER}:${HTTP_AUTH_PASS}`);
  }

  // Set payment secret header value from browser local storage to enable test
  // payment mode.
  if (PAYMENT_SECRET_HEADER_NAME) {
    try {
      const { localStorage } = window;
      superagent.set(PAYMENT_SECRET_HEADER_NAME, localStorage.getItem(PAYMENT_SECRET_HEADER_NAME));
    } catch (e) {
      // Access denied - localStorage is disabled.
    }
  }

  return superagent;
};

module.exports = {
  // Default version uses global next/config package to read configuration.
  request: initSuperagent(nextConfig),
  // Initializes superagent from custom config. Useful when global next/config
  // is not available.
  getRequest: config => initSuperagent(config),
};
