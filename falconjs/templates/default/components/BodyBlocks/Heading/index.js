import React from 'react';
import PropTypes from 'prop-types';

const Heading = ({ title, Tag }) => (
  <div className={`bb bb-heading ${Tag}`}>
    <div className="container">
      <Tag>{title}</Tag>
    </div>
  </div>
);

Heading.propTypes = {
  Tag: PropTypes.oneOf(['h1', 'h2', 'h3', 'h4', 'h5', 'h6']),
  title: PropTypes.string.isRequired,
};

Heading.defaultProps = {
  Tag: 'h2',
};

export default Heading;
