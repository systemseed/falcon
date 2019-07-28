const pathToRegexp = require('path-to-regexp');
const { parse } = require('url');

/**
 * Finds internal route in the pages available at the frontend app only.
 * @param URL
 *   Full URL string. I.e. /content/one/two?page=1.
 * @param routes
 *   List of routes.
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

module.exports = matchAppOnlyRoute;
