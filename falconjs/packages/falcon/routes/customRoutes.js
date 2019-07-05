const pathToRegexp = require('path-to-regexp');
const debug = require('debug')('falcon:routing/customRoutes');
const { parse } = require('url');

// This file defines frontend only routes.
// Routing checks for these routes first, and if the route matches
// frontend only routes, then there will be no backend request to check the
// page content.


/**
 * Finds internal route in the pages available at the frontend app only.
 * @param URL
 *   Full URL string. I.e. /content/one/two?page=1.
 * @returns {*}
 *   Matched route & route query with dynamic values from the URL.
 */
const matchAppOnlyRoute = (URL, routes) => {
  // Get object with different parts of the URL.
  const parsedUrl = parse(URL, true);

  // By default pass into the request the same query details as the
  // browser URL has.
  const hrefQuery = parsedUrl.query;

  // Finds matching route using regexp.
  const machingRoute = routes.find((route) => {
    const keys = [];
    const regex = pathToRegexp(route.path, keys);
    let values = regex.exec(parsedUrl.pathname);
    if (values) {
      // Remove the first array value as it always contains the full url string.
      values = values.slice(1);

      // Adds dynamic parts of the url to the query object.
      keys.forEach((key, index) => {
        hrefQuery[key.name] = decodeURIComponent(values[index]);
      });

      return true;
    }
    return false;
  });

  // Add a little flag that tells _app.js to not make a request to the backend
  // searching for the matching entity.
  hrefQuery.isAppOnlyRoute = true;

  if (machingRoute) {
    return { href: machingRoute.route, query: hrefQuery };
  }

  return null;
};

/**
 * Converts object of URL query values into query string
 * valid for the browser.
 */
const toQuerystring = obj => Object.keys(obj)
  .filter(key => obj[key] !== null && obj[key] !== undefined)
  .map((key) => {
    let value = obj[key];

    if (Array.isArray(value)) {
      value = value.join('/');
    }
    return [
      encodeURIComponent(key),
      encodeURIComponent(value),
    ].join('=');
  }).join('&');


function customRoutes(nextApp, routes) {
  return async function checkRoutes(req, res, next) {
    // Check if the current URL matches any of frontend only routes. If yes,
    // then use a usual Next.js render handler to render the page.
    try {
      const route = matchAppOnlyRoute(req.url, routes);
      if (route) {
        debug('The route for the path %s was found internally. Using file ./pages%s.js to render the page.', req.url, route.href);
        // eslint-disable-next-line no-return-await
        return await nextApp.render(req, res, route.href, route.query);
      }
    } catch (error) {
      debug('Error during searching for the internal route. Error message: %o', error);
    }
    return next();
  };
}

// Export helpers.
module.exports = {
  matchAppOnlyRoute,
  toQuerystring,
  customRoutes,
};
