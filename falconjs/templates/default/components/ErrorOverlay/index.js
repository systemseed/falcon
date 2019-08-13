import React from 'react';
import PropTypes from 'prop-types';

import Link from '@systemseed/falcon/components/Link';

const ErrorOverlay = ({ statusCode, homeNextLink }) => (
  <div>

    {statusCode === 404 ? (
      <div className="error-info">
        <p>
          It looks like you’re lost.<br /><br />
          The page you have requested does not exist.
          You may have followed an outdated link or mistyped a URL.
        </p>

        {homeNextLink && (
        <Link {...homeNextLink}>
          <a> Go to home page</a>
        </Link>
        )}
      </div>
    ) : (
      <>
        <h2>{statusCode}</h2>
        Something went wrong. Please try again.
      </>
    )}

  </div>
);

ErrorOverlay.propTypes = {
  statusCode: PropTypes.number.isRequired,
  homeNextLink: PropTypes.shape(),
};

ErrorOverlay.defaultProps = {
  // In SSR, headerSettings aren't available
  homeNextLink: null,
};

export default ErrorOverlay;
