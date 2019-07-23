import React from 'react';
import { Provider } from 'react-redux';
import withRedux from 'next-redux-wrapper';
import * as settingsActions from '@systemseed/falcon/redux/actions/globalSettings';
import App, { Container } from 'next/app';
import { getHomepageLink } from '@systemseed/falcon/routes/globalSettings';
import { matchAppOnlyRoute } from '@systemseed/falcon/routes/frontendOnlyRoutes';
import getPageContent from '@systemseed/falcon/routes/getPageContent';
import configureStore from '../store/store';
import normalizeURL from '../utils/normalizeURL';
import routes from '../routes/routes';

// Internal debugging.
const debug = require('debug')('falcon:_app.js');

class Application extends App {
  /**
   * See https://nextjs.org/docs#fetching-data-and-component-lifecycle
   * for more details.
   */
  static async getInitialProps({ Component, ctx: { res, query, ...ctx }, ...appProps }) {
    const { store } = ctx;

    // Remove legacy router prop passed to the app. Use withRouter() HOC
    // if you need router API in your component.
    const { router, ...props } = appProps;

    let initialProps = {
      entity: null,
      settings: null,
    };

    // If it's a backend request then settings should come from the backend.
    // We put the data into the redux store so that later we can take it
    // from store instead of making additional backend requests.
    if (res && res.settings) {
      initialProps.settings = res.settings;
      store.dispatch(settingsActions.save(res.settings));
    } else {
      // Restore global settings object from the temporary redux store.
      const { globalSettings } = store.getState();
      initialProps.settings = globalSettings;
    }

    const url = ctx.asPath || ctx.pathname;

    // The flag isAppOnlyRoute appears automatically in the query if the
    // current page was found in the internal routing. See ./routing/routes.js.
    let isAppRoute = false;
    if (query.isAppOnlyRoute) {
      isAppRoute = true;
    }

    // Make sure that current route is not only app route.
    // Covers case when query param not passed.
    if (!isAppRoute && matchAppOnlyRoute(url, routes)) {
      isAppRoute = true;
    }

    // The flag is true if this is standard Next.js error page.
    const isErrorPage = router.route === '/_error';

    // If the current page is not the frontend only route and not an error page,
    // then handle content from Drupal route.
    if (!isAppRoute && !isErrorPage) {
      try {
        // If it is a backend response, then entity object must be a part of
        // response. See decoupledRouter() for details.
        if (res && res.entity) {
          initialProps.entity = res.entity;
        } else {
          let normalizedURL = url;
          // Get raw link of the homepage (i.e. it will include "/home" instead of "/").
          const homepageLink = getHomepageLink(initialProps.settings, true);
          if (homepageLink && url === '/') {
            normalizedURL = homepageLink.url;
          } else {
            normalizedURL = normalizeURL(url);
          }

          const { statusCode, entity } = await getPageContent(normalizedURL);
          initialProps.statusCode = statusCode;
          initialProps.entity = entity;
        }
      } catch (error) {
        initialProps.statusCode = 500;
        debug(error);
      }
    }

    // Call to getInitialProps() from the Page component if exists.
    if (Component.getInitialProps) {
      try {
        // Merge original props with props returned from page component.
        const childInitialProps = await Component.getInitialProps({
          res,
          query,
          router,
          ...initialProps,
          ...props,
          ...ctx,
        });
        initialProps = { ...initialProps, ...childInitialProps };
      } catch (e) {
        initialProps.statusCode = 500;
        debug(e);
      }
    }

    // Add status code from response if it was not set explicitly.
    if (!initialProps.statusCode && res) {
      res.statusCode = res.statusCode || 200;
      initialProps.statusCode = res.statusCode;
    }

    // If no errors occured, we assume that everything went well.
    initialProps.statusCode = initialProps.statusCode || 200;

    return initialProps;
  }

  render() {
    const { Component, store, ...props } = this.props;

    return (
      <Container>
        <Provider store={store}>
          <Component {...props} />
        </Provider>
      </Container>
    );
  }
}

export default withRedux(configureStore)(Application);
