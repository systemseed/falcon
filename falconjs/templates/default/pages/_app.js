import React from 'react';
import withRedux from 'next-redux-wrapper';
import App from 'next/app';
import withFalcon from '@systemseed/falcon/components/withFalcon';
import transformBodyBlocks from '@systemseed/falcon/utils/transformBodyBlocks';
import * as field from '@systemseed/falcon/utils/transforms.fields';
import configureStore from '../store/store';
import * as transformsSettings from '../utils/transforms.settings';
import transformsBlocks from '../utils/transforms.blocks';
import ErrorPage from './_error';
import Header from '../components/Header';
import Footer from '../components/Footer';
import HtmlHead from '../components/HtmlHead';

import '../static/_styles.scss';

const debug = require('debug')('falcon:_app.js');

// Internal debugging.
// const debug = require('debug')('falcon:_app.js');

class Application extends App {
  /**
   * See https://nextjs.org/docs#fetching-data-and-component-lifecycle
   * for more details.
   */
  static async getInitialProps({ entity, settings }) {
    const initialProps = {};

    // Pass entity, paragraph and metatags as props.
    if (entity) {
      try {
        // Transform paragraphs on the backend into body blocks on the frontend.
        const blocks = field.getArrayValue(entity, 'field_body_blocks');
        initialProps.blocks = transformBodyBlocks(entity, blocks, transformsBlocks);
      } catch (e) {
        debug('Could not transform entity. Error: %s', e);
      }
    }

    // Pass transformed global settings as props as well.
    if (settings) {
      try {
        initialProps.headerSettings = transformsSettings.header(settings);
      } catch (e) {
        debug('Could not transform header. Error: %s', e);
      }

      try {
        initialProps.footerSettings = transformsSettings.footer(settings);
      } catch (e) {
        debug('Could not transform footer. Error: %s', e);
      }
    }
    return initialProps;
  }

  render() {
    const {
      Component,
      store,
      headerSettings,
      footerSettings,
      statusCode,
      metatags,
      ...props
    } = this.props;

    return (
      <>
        <HtmlHead metatags={metatags} />
        {headerSettings && <Header {...headerSettings} />}

        {statusCode === 200 && <div className="page-content"><Component {...props} /></div>}

        {statusCode !== 200
        && (
          <ErrorPage
            statusCode={statusCode}
            homeNextLink={headerSettings ? headerSettings.homeNextLink : null}
          />
        )}

        {footerSettings && <Footer {...footerSettings} />}
      </>
    );
  }
}

export default withRedux(configureStore)(withFalcon(Application));
