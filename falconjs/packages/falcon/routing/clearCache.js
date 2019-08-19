const cache = require('memory-cache');

function clearCache(req, res) {
  cache.clear();
  return res.status(200).send('Cleared');
}

module.exports = clearCache;
