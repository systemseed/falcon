const { isInternalUrl } = require('next-server/dist/server/utils');
const { parse } = require('url');

function internalRoutes(nextApp) {
  return async function internalRoutesMiddleware(req, res, next) {
    const parsedUrl = parse(req.url, true);

    // Grab default next.js request handler.
    const nextRequestHandler = nextApp.getRequestHandler();

    // If the current request is internal next.js url then just handle pages
    // using default handlers.
    if (isInternalUrl(parsedUrl.pathname)) {
      return nextRequestHandler(req, res);
    }
    return next();
  };
}

module.exports = internalRoutes;
