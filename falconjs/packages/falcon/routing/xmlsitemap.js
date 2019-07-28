const cache = require('memory-cache');
const { getRequest } = require('../request/request.node');

const xmlSitemapProxy = async (req, res, app) => {
  let xmlSitemapData = cache.get('xmlSitemapData');

  if (!xmlSitemapData) {
    const superAgent = getRequest(app.nextConfig);
    const sitemapResp = await superAgent
      .get('/concern/sitemap.xml')
      .set('Accept', 'application/xml');

    xmlSitemapData = sitemapResp.body;
    const cacheTTL = process.env.NODE_CACHE_TTL !== undefined
      ? Number(process.env.NODE_CACHE_TTL)
      : 1000;
    cache.put('xmlSitemapData', xmlSitemapData, cacheTTL);
  }

  res
    .status(200)
    .type('application/xml')
    .send(xmlSitemapData);
};

module.exports = xmlSitemapProxy;
