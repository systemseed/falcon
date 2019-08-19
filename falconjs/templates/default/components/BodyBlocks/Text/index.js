import React from 'react';
import PropTypes from 'prop-types';

const Text = ({ text }) => (
  <div className="bb bb-text">
    {/* eslint-disable-next-line react/no-danger */}
    <div className="container" dangerouslySetInnerHTML={{ __html: text }} />
  </div>
);

Text.propTypes = {
  text: PropTypes.string.isRequired,
};

export default Text;
