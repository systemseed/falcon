import React from 'react';
import PropTypes from 'prop-types';
import HeaderMenu from './HeaderMenu';

const Header = ({ menu, logo, siteName }) => (
  <header>
    {logo.url && <img src={logo.url} alt={logo.alt} title={siteName} />}
    <HeaderMenu menu={menu} />
  </header>
);

Header.propTypes = {
  menu: PropTypes.arrayOf(PropTypes.shape({
    label: PropTypes.string,
    nextLink: PropTypes.shape({
      url: PropTypes.string,
      href: PropTypes.string,
      as: PropTypes.string,
    }),
  })),
  logo: PropTypes.shape({
    alt: PropTypes.string,
    url: PropTypes.string,
  }),
  siteName: PropTypes.string,
};

Header.defaultProps = {
  menu: [],
  logo: {},
  siteName: '',
};

export default Header;
