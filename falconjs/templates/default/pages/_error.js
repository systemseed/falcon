import React from 'react';

import ErrorOverlay from '../components/ErrorOverlay';

const ErrorPage = props => (
  <div className="error-page">
    <ErrorOverlay {...props} />
  </div>
);

export default ErrorPage;
