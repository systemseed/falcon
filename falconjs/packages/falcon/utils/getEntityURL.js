const matchAppOnlyRoute = require('./matchAppOnlyRoute');
const toQuerystring = require('./toQuerystring');
const { APP_ONLY_ROUTES } = require('./constants');

/**
 * Return parameters to apply to the <Link> component of
 * Next.js to make the routing working properly.
 */
const getEntityURL = (entity) => {
  const entityURL = {

    // Value which ALWAYS represents client facing URL that you see in the browser.
    // If it's null, then something went wrong and this entity does not have URL.
    url: null,

    // Value which ALWAYS represents internal Next.js route for the current
    // entity. If it's null, then the url should be treated as external.
    route: null,

    // Info if the link is considered as external by Drupal or not.
    isExternal: false,

    // The next two params are internal route + human readable URL for
    // Next.js <Link /> component. It's expected to pass the result of this
    // function to the link just like that <Link {...entityURL} /> to make
    // it render the link with the right internal routing.
    // Props from above "url" and "route" will be ignored by the component.
    // If you add here more <Link /> props they will be automatically
    // applied inside of Link components.
    href: null,
    as: null,
  };

  if (!entity || !entity.hasOwnProperty('url')) {
    return entityURL;
  }

  // It's assumed that every entity has url property passed from the backend
  // as an object with URL and it's metadata.
  const { url } = entity;

  // If URL is empty obviously there is nothing we can do anymore here.
  if (!(url && url.hasOwnProperty('url'))) {
    return entityURL;
  }

  // Copy URL value into the object. It is always client facing URL.
  entityURL.url = url.url;

  // Default href to the url value for <Link> rendering properly.
  entityURL.href = entityURL.url;

  // If the URL is external then we're done here, no need to modify the
  // object any further.
  if (url.hasOwnProperty('is_external') && url.is_external) {
    entityURL.isExternal = true;
    return entityURL;
  }

  url.entity_type = url.entity_type || '';
  url.entity_bundle = url.entity_bundle || '';

  // If the URL object from the backend has metadata regarding entity type / bundle
  // then we can build the internal page route based on that.
  if (url.entity_type !== '' && url.entity_bundle !== '') {
    const route = `/${url.entity_type}/${url.entity_bundle}`;
    entityURL.route = route;
    entityURL.href = route;
    entityURL.as = entityURL.url;
  } else {
    // If the path does not have a corresponding entity on the backend it still
    // can be an internal route which exists in the frontpage app only.
    const route = matchAppOnlyRoute(entityURL.url, APP_ONLY_ROUTES);
    if (route) {
      entityURL.route = route.href;
      entityURL.href = `${route.href}${route.query ? `?${toQuerystring(route.query)}` : ''}`;
      entityURL.as = entityURL.url;
    }
  }

  return entityURL;
};

module.exports = getEntityURL;
