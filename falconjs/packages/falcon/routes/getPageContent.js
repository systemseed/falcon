const Router = require('next/router').default;
const { parse } = require('url');
const debug = require('debug')('cw:routing');
const { request: defaultRequest } = require('../request/request.node');
const getEntityURL = require('../utils/getEntityURL');

/**
 * Returns Drupal entity data for both server side and client side.
 */
async function getPageContent(requestedURL, res = null, request = null) {
  const props = {
    statusCode: null,
    entity: null,
    entityURL: null,
  };

  const superagent = request || defaultRequest;

  // Get object with different parts of the URL.
  const parsedUrl = parse(requestedURL, true);

  const entityURL = res && res.normalizedRequestPath
    ? res.normalizedRequestPath : parsedUrl.pathname;

  debug('Requesting entity with URL %s from the Drupal backend...', entityURL);

  // Lookup for entity metadata using Json Recursive module format.
  // Here we're using standard Drupal urls + ?_format=* feature.
  const entityResponse = await superagent
    .get(entityURL)
    // Tell superagent to consider any valid Drupal response as successful.
    // Later we can capture error codes if needed.
    .ok(response => response.statusCode)
    .query({ _format: 'json_recursive' });

  // Catch all 40x and 50x errors during request to fetch entity data for the given url.
  if (entityResponse.statusCode >= 400) {
    if (res) res.status(entityResponse.statusCode);
    props.statusCode = entityResponse.statusCode;
    debug('Entity request to the backend caught %s response code.', entityResponse.statusCode);
  }

  if (entityResponse.statusCode === 200) {
    // Get entity & setting objects from the request and pass it to the
    // pages as a part of server response object.
    props.entity = entityResponse.body;

    props.entityURL = getEntityURL(props.entity);

    // If entity URL object does not have URL or internal next.js route,
    // then we can't render the page.
    if (!(props.entityURL.url && props.entityURL.route)) {
      debug('Entity URL / route values are empty: %o', props.entityURL);
      debug('Entity object: %o', res.entity);
      if (res) res.status(500).end('Internal Routing Error.');
      props.statusCode = 500;
      return props;
    }

    // The type of request we do to the backend to determine if the url
    // exists on the backend automatically follows 301/302 redirects. So
    // if that happened, then entity path alias will be different from what
    // the user originally requested, which means that we need to redirect
    // them to the right path alias.
    if (entityURL !== props.entityURL.url && entityURL !== '/') {
      // Perform redirect to the canonical URL if the original URL is different.
      debug('Entity URL does not match the requested URL. Redirecting from %s to %s.', entityURL, props.entityURL.url);

      if (res) {
        // Server level redirect.
        res.redirect(301, props.entityURL.url);
      } else {
        // Client level redirect.
        Router.push(props.entityURL.route, props.entityURL.url);
      }

      return props;
    }

    // Helper message to understand the entry point for the page render.
    debug('All good, building page using ./pages%s.js file as an entry point.', props.entityURL.route);

    props.statusCode = 200;
  }

  return props;
}

module.exports = getPageContent;
