const debug = require('debug')('falcon:routing/frontendOnlyRoutes');
const matchAppOnlyRoute = require('../utils/matchAppOnlyRoute');

function frontendOnlyRoutes(nextApp, routes) {
  return async function frontendOnlyRoutesMiddleware(req, res, next) {
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

module.exports = frontendOnlyRoutes;
