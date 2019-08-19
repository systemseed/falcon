import React from 'react';
import PropTypes from 'prop-types';
import DesktopNavigation from './DesktopNavigation';

const Header = ({ menu, logo, siteName, homeNextLink }) => (
  <header>
    <DesktopNavigation menu={menu} logo={logo} siteName={siteName} homeNextLink={homeNextLink} />
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
  homeNextLink: PropTypes.shape({
    url: PropTypes.string,
    as: PropTypes.string,
    href: PropTypes.string,
    route: PropTypes.string,
  }),
};

Header.defaultProps = {
  menu: [],
  logo: {},
  siteName: '',
  homeNextLink: null,
};

export default Header;
