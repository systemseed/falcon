const getEntityURL = require('./getEntityURL');

/**
 * Returns the link object to the homepage.
 */
const getHomepageLink = (settings, isRaw = false) => {
  const globalSettings = settings || {};

  // Link to the front page of the website.
  if (globalSettings.field_frontpage && typeof globalSettings.field_frontpage === 'object'
    && typeof globalSettings.field_frontpage[0] === 'object') {
    const nextLink = getEntityURL(globalSettings.field_frontpage[0]);
    if (nextLink.url) {
      // Normally the homepage link should have "/" as the URL. However,
      // there are several cases in the routing when we need to get the
      // "unmasked" url of the node on the Drupal for the home page. "isRaw"
      // flag enables the behavior of keeping the original node URL instead of
      // masking it.
      if (!isRaw) {
        nextLink.url = '/';
        nextLink.as = '/';
      }
      return nextLink;
    }
  }

  return null;
};

module.exports = getHomepageLink;
