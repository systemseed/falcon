const { parse } = require('url');
const debug = require('debug')('falcon:routing/decoupledRouter');
const { getRequest } = require('../request/request.node');
const getEntityContent = require('./getEntityContent');

/**
 * @file
 * This file includes code that is supposed to be executed on
 * server-side only, thus we don't provide cross-browser compatibility neither
 * for the code nor for the imports above.
 */


/**
 * Handles server routing for the application.
 *
 * General approach: search page on the Drupal backend and render the page
 * or throw 404 accordingly.
 */
async function decoupledRouter(req, res, nextApp) {
  // Get object with different parts of the URL.
  const parsedUrl = parse(req.url, true);

  // Initialize server version of superagent with the given config.
  const request = getRequest(nextApp.nextConfig);

  // At this point the last place where we can find the requested URL is
  // drupal backend.
  try {
    // Make necessary requests to the backend to fetch the page content.
    // eslint-disable-next-line max-len
    const { statusCode, entity, entityURL } = await getEntityContent(req.url, res, request);

    // Handle Page Not Found response.
    if (statusCode === 404) {
      return await nextApp.render404(req, res, parsedUrl);
    }

    // Handle any other error.
    if (statusCode >= 400) {
      return await nextApp.renderError(null, req, res, parsedUrl.pathname, parsedUrl.query);
    }

    // If everything is good then tell Next.js what internal route to use
    // as an entry point for the content rendering.
    if (statusCode === 200) {
      if (!('falcon' in res)) {
        res.falcon = {};
      }
      res.falcon.entity = entity;
      return await nextApp.render(req, res, entityURL.route, parsedUrl.query);
    }
  } catch (error) {
    debug('Error during server routing handling. Error message: %o', error);
    res.status(500);
    return res.end('Internal Server Error');
  }

  // Not sure how may come to this case, but putting it here to catch
  // outstanding issues if there are any.
  debug('Using default Next.js routes handler.');
  return nextApp.getRequestHandler()(req, res);
}

module.exports = decoupledRouter;
