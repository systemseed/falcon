import React from 'react';
import PropTypes from 'prop-types';
import Link from 'next/link';

const LandingPage = ({ entity }) => (
  <div>
    {entity && entity.title[0].value}
    <br />
    <Link as="/cookie-table" href="/custom-route" prefetch>
      <a href="/cookie-table">Cookie-table</a>
    </Link>
    <br />
    <Link as="/" href="/node/page" prefetch>
      <a>home</a>
    </Link>
    <br />
    <Link as="/demo_page2" href="/node/page" prefetch>
      <a>Page2</a>
    </Link>
  </div>
);

LandingPage.defaultProps = {
  entity: '',
};

LandingPage.propTypes = {
  entity: PropTypes.shape({
    title: PropTypes.array,
  }),
};

export default LandingPage;
