import React from 'react';
import Router from 'next/router';
import { Provider } from 'react-redux';
import withRedux from 'next-redux-wrapper';
import * as settingsActions from '@systemseed/falcon/redux/actions/globalSettings';
import getHomepageLink from '@systemseed/falcon/utils/getHomepageLink';
import matchAppOnlyRoute from '@systemseed/falcon/utils/matchAppOnlyRoute';
import getEntityContent from '@systemseed/falcon/routing/getEntityContent';
import normalizeURL from '../utils/normalizeURL';
import routes from '../routes/routes';
import * as field from '../utils/transforms.fields';
import * as transformsSettings from '../utils/transforms.settings';
import TransformBlocks from '../utils/transforms.blocks';

import '../static/_styles.scss';

// Internal debugging.
const debug = require('debug')('falcon:_app.js');

export default class WithFalcon extends React.Component {
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
    if (res && res.falcon && res.falcon.settings) {
      initialProps.settings = res.falcon.settings;
      store.dispatch(settingsActions.save(res.falcon.settings));
    } else {
      // Restore global settings object from the temporary redux store.
      const { globalSettings } = store.getState();
      initialProps.settings = globalSettings;
    }

    if (initialProps.settings) {
      try {
        initialProps.headerSettings = transformsSettings.header(initialProps.settings);
      } catch (e) {
        debug('Could not transform header. Error: %s', e);
      }

      try {
        initialProps.footerSettings = transformsSettings.footer(initialProps.settings);
      } catch (e) {
        debug('Could not transform footer. Error: %s', e);
      }
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
        if (res && res.falcon && res.falcon.entity) {
          initialProps.entity = res.falcon.entity;
        } else {
          let normalizedURL = url;
          // Get raw link of the homepage (i.e. it will include "/home" instead of "/").
          const homepageLink = getHomepageLink(initialProps.settings, true);

          if (homepageLink.url === url) {
            Router.push(homepageLink.route, '/');
          } else if (homepageLink && url === '/') {
            normalizedURL = homepageLink.url;
          } else {
            normalizedURL = normalizeURL(url);
          }

          const { statusCode, entity } = await getEntityContent(normalizedURL);
          initialProps.statusCode = statusCode;
          initialProps.entity = entity;
        }
      } catch (error) {
        initialProps.statusCode = 500;
        debug(error);
      }
    }

    // Pass entity, paragraph and metatags as props.
    if (initialProps.entity) {
      try {
        // Transform paragraphs on the backend into body blocks on the frontend.
        const blocks = field.getArrayValue(initialProps.entity, 'field_body_blocks');
        initialProps.blocks = new TransformBlocks().transform(initialProps.entity, blocks);

        // Get tags data from the entity and pass as props.
        if (initialProps.entity.hasOwnProperty('metatag_normalized')) {
          initialProps.metatags = initialProps.entity.metatag_normalized;
        }
      } catch (e) {
        debug('Could not transform entity. Error: %s', e);
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
    const { children } = this.props;
    return children;
  }
}
