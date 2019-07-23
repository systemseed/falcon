const cache = require('memory-cache');
const Router = require('next/router').default;
const { parse } = require('url');
const debug = require('debug')('falconjs:routing/globalSettings');
const { request: defaultRequest, getRequest } = require('../request/request.node.js');
const getEntityURL = require('../utils/getEntityURL');

/**
 * Loads global site settings (menu, header, footer) from Drupal backend.
 */
async function getSettings(settingsName, request = null, res = null) {
  const props = {
    statusCode: null,
    settings: null,
  };

  const superagent = request || defaultRequest;

  debug(`Requesting Global settings from the Drupal backend with name ${settingsName}...`);
  const settingsResponse = await superagent
    .get(`/config_pages/${settingsName}`)
    // Tell superagent to consider any valid Drupal response as successful.
    // Later we can capture error codes if needed.
    .ok(response => response.statusCode)
    .query({ _format: 'json_recursive' });

  // Catch all 40x and 50x errors during request to fetch global settings.
  if (settingsResponse.statusCode >= 400) {
    if (res) res.status(settingsResponse.statusCode);
    props.statusCode = settingsResponse.statusCode;
    debug('Global settings request to the backend caught %s error. Response: %o', settingsResponse.statusCode, settingsResponse);
  }

  // Get global settings object from the request and pass it to the
  // pages as a part of server response object.
  if (settingsResponse.statusCode === 200) {
    props.settings = settingsResponse.body;
    props.statusCode = 200;
  }

  return props;
}

/**
 * Returns the link object to the homepage.
 */
const getHomepageLink = (settings, isRaw = false) => {
  const globalSettings = settings || {};

  // Link to the front page of the website.
  if (globalSettings.field_frontpage && typeof globalSettings.field_frontpage === 'object'
    && typeof globalSettings.field_frontpage[0] === 'object') {
    const nextLink = getEntityURL(globalSettings.field_frontpage[0]);
    if (nextLink.url) {
      // Normally the homepage link should have "/" as the URL. However,
      // there are several cases in the routing when we need to get the
      // "unmasked" url of the node on the Drupal for the home page. "isRaw"
      // flag enables the behavior of keeping the original node URL instead of
      // masking it.
      if (!isRaw) {
        nextLink.url = '/';
        nextLink.as = '/';
      }
      return nextLink;
    }
  }

  return null;
};

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
      // At this point we already know that the page needs to be rendered either
      // using internal routing of the app or using Drupal routing. So we can fetch
      // global page settings (menu, header, footer) from backend or cache.
      try {
        // Use very simple in-memory cache storage for caching of global settings
        // from Drupal. You can force flush it by restarting a server or
        // just requesting /_cacheclear page if clearCache middleware is using.
        const settingsFromCache = cache.get(settingsName);
        if (settingsFromCache) {
          res.settings = settingsFromCache;
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
          res.settings = settings;
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

function homePageInSettings(req, res, next) {
  if (res.settings) {
    // Get object with different parts of the URL.
    const parsedUrl = parse(req.url, true);

    // Get raw link of the homepage (i.e. it will include "/home" instead of "/").
    const homepageLink = getHomepageLink(res.settings, true);
    // If the user requested page which is configured on the backend to be
    // the homepage of the application, then we need to force redirect him
    // to the homepage instead.
    if (homepageLink && parsedUrl.pathname === homepageLink.url) {
      debug('The requested page %s is an alias of the front page. Redirecting to the front page.', homepageLink.url);

      if (res) {
        // Server level redirect.
        res.redirect(301, '/');
      } else {
        // Client level redirect.
        Router.push(homepageLink.route, '/');
      }
    }

    // The homepage on the Drupal backend is not "/" but some other alias,
    // so in case of the front page we need to request the node with the right alias.
    if (homepageLink && parsedUrl.pathname === '/') {
      debug('Home page requested. Using %s page in Drupal for the homepage as defined in global settings.', homepageLink.url);
      res.normalizedRequestPath = homepageLink.url;
    }
  }

  return next();
}

module.exports = {
  globalSettingsForApp,
  getSettings,
  getHomepageLink,
  homePageInSettings,
};
