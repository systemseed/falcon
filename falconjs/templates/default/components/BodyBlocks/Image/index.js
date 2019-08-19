import React from 'react';
import PropTypes from 'prop-types';

const Image = ({ image }) => (
  <div className="bb bb-image">
    <img
      src={image.url}
      alt={image.alt}
    />
  </div>
);

Image.propTypes = {
  image: PropTypes.shape({
    url: PropTypes.string.isRequired,
    alt: PropTypes.string,
  }).isRequired,
};

export default Image;
