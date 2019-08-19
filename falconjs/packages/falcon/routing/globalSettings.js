const cache = require('memory-cache');
const { parse } = require('url');
const debug = require('debug')('falconjs:routing/globalSettings');
const { getRequest } = require('../request/request.node.js');
const getHomepageLink = require('../utils/getHomepageLink');
const getSettings = require('../utils/getSettings');

/**
 * Adds settings from the backend to res.settings.
 *
 * @param nextApp
 * @param settingsName - Config page entity id.
 * @param nodeCacheTTL
 */
function globalSettingsForApp(nextApp, settingsName, nodeCacheTTL = 1000) {
  return async function globalSettingsMiddleware(req, res, next) {
    // Get object with different parts of the URL.
    const parsedUrl = parse(req.url, true);

    // Initialize server version of superagent with the given config.
    const request = getRequest(nextApp.nextConfig);

    if (settingsName) {
      if (!('falcon' in res)) {
        res.falcon = {};
      }

      // At this point we already know that the page needs to be rendered either
      // using internal routing of the app or using Drupal routing. So we can fetch
      // global page settings (menu, header, footer) from backend or cache.
      try {
        // Use very simple in-memory cache storage for caching of global settings
        // from Drupal. You can force flush it by restarting a server or
        // just requesting /_cacheclear page if clearCache middleware is using.
        const settingsFromCache = cache.get(settingsName);
        if (settingsFromCache) {
          res.falcon.settings = settingsFromCache;
        } else {
          const { statusCode, settings } = await getSettings(settingsName, request, res);

          // Handle Page Not Found response.
          if (statusCode === 404) {
            return await nextApp.render404(req, res, parsedUrl);
          }

          // Handle any other error.
          if (statusCode >= 400) {
            return await nextApp.renderError(null, req, res, parsedUrl.pathname, parsedUrl.query);
          }

          // Time to live (in ms) for cache of global settings. The object will be
          // stored in node.js memory.
          cache.put(settingsName, settings, Number(nodeCacheTTL));

          // Pass settings data in the response object.
          // It will be available in getInitialProps() of any page or _app.js file.
          res.falcon.settings = settings;
        }
      } catch (error) {
        debug('Error during global settings fetch. Error message: %o', error);
        res.status(500);
        return res.end('Internal Server Error');
      }
    }

    return next();
  };
}

function handleHomepageRequest(req, res, next) {
  if (res.falcon && res.falcon.settings) {
    // Get object with different parts of the URL.
    const parsedUrl = parse(req.url, true);

    // Get raw link of the homepage (i.e. it will include "/home" instead of "/").
    const homepageLink = getHomepageLink(res.falcon.settings, true);
    // If the user requested page which is configured on the backend to be
    // the homepage of the application, then we need to force redirect him
    // to the homepage instead.
    if (homepageLink && parsedUrl.pathname === homepageLink.url) {
      debug('The requested page %s is an alias of the front page. Redirecting to the front page.', homepageLink.url);
      res.redirect(301, '/');
    }

    // The homepage on the Drupal backend is not "/" but some other alias,
    // so in case of the front page we need to request the node with the right alias.
    if (homepageLink && parsedUrl.pathname === '/') {
      debug('Home page requested. Using %s page in Drupal for the homepage as defined in global settings.', homepageLink.url);
      res.falcon.normalizedRequestPath = homepageLink.url;
    }
  }

  return next();
}

module.exports = {
  globalSettingsForApp,
  handleHomepageRequest,
};
