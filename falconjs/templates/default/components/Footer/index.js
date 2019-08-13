import React from 'react';
import PropTypes from 'prop-types';

const Footer = ({ text }) => (
  <footer>
    {text}
  </footer>
);

Footer.propTypes = {
  text: PropTypes.string,
};

Footer.defaultProps = {
  text: '',
};

export default Footer;
