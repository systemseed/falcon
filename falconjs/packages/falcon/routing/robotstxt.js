const debug = require('debug')('falconjs:routing/robotstxt');
const { getTextValue } = require('../utils/transforms.fields');

const robotsTxtProxy = (req, res, app, falconConfig) => {
  try {
    let robotsTxt = getTextValue(res.falcon.settings, 'field_robots_txt');
    robotsTxt += `\r\nSitemap: ${falconConfig.FRONTEND_URL}/sitemap.xml`;

    res
      .status(200)
      .type('text/plain;charset=UTF-8')
      .send(robotsTxt);
  } catch (error) {
    debug('%O', debug);
    res.status(404);
  }
};

module.exports = robotsTxtProxy;
