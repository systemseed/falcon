import React from 'react';
import App, { Container } from 'next/app';

// Internal debugging.
const debug = require('debug')('falcon:_app.js');

class Application extends App {
  /**
   * See https://nextjs.org/docs#fetching-data-and-component-lifecycle
   * for more details.
   */
  static async getInitialProps({ Component, ctx: { res, ...ctx } }) {

    // If it's a backend request then settings should come from the backend.
    // We put the data into the redux store so that later we can take it
    // from store instead of making additional backend requests.
    if (res && res.settings) {
      console.log(res.settings);
    }
    if (res && res.entity) {
      console.log(res.entity);
    }
    const pageProps = await Component.getInitialProps(ctx);
    return pageProps;
  }

  render() {
    const { Component } = this.props;

    return (
      <Container>
        <Component {...this.props} />
      </Container>
    );
  }
}

export default Application;
