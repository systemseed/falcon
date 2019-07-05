import React from 'react';
import Link from 'next/link';
import routing from '@systemseed/falcon/routing';
import request from '@systemseed/falcon/request';

// Test.
routing();

class LandingPage extends React.Component {
  static async fetchData() {
    const response = await request.get('/jsonapi/node/appeal');
    //console.log(response.body); // eslint-disable-line
  }

  render() {
    //LandingPage.fetchData();
    return (
      <div>
        AAAA
        <br />
        <Link url="/cookie-table" prefetch>
          <a href="/cookie-table">Cookie-table</a>
        </Link>
        <br />
        <Link url="/page2" prefetch>
          <a href="/page2">Page2</a>
        </Link>
      </div>
    );
  }
}

LandingPage.getInitialProps = async () => {
  return {};
};

export default LandingPage;
