const cache = require('memory-cache');
const { getRequest } = require('../request/request.node');

const debug = require('debug')('falconjs:routing/xmlsitemap');

const xmlSitemapProxy = async (req, res, app) => {
  try {
    let xmlSitemapData = cache.get('xmlSitemapData');

    if (!xmlSitemapData) {
      const superAgent = getRequest(app.nextConfig);
      const response = await superAgent
        .get('/falcon/sitemap.xml')
        .set('Accept', 'application/xml');

      xmlSitemapData = response.body;
      const cacheTTL = process.env.NODE_CACHE_TTL !== undefined
        ? Number(process.env.NODE_CACHE_TTL)
        : 1000;
      cache.put('xmlSitemapData', xmlSitemapData, cacheTTL);
    }

    res
      .status(200)
        .type('application/xml')
        .send(xmlSitemapData);
  } catch (error) {
    debug('%O', debug);
    res.status(404);
  }
};

module.exports = xmlSitemapProxy;
