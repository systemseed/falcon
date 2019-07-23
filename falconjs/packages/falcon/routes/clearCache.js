const cache = require('memory-cache');
const { parse } = require('url');

function clearCache(req, res, next) {
  // Get object with different parts of the URL.
  const parsedUrl = parse(req.url, true);

  // A little helper path to clear cached data.
  if (parsedUrl.pathname === '/_cacheclear') {
    cache.clear();
    return res.status(200).send('Cleared');
  }

  return next();
}

module.exports = clearCache;
