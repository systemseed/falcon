import React from 'react';
import PropTypes from 'prop-types';
import { withRouter } from 'next/router';
import {
  Image,
  Container,
  Menu,
  Responsive,
} from 'semantic-ui-react';
import Link from '@systemseed/falcon/components/Link';
import getWidth from '../../utils/getWidth';

const DesktopNavigation = ({ menu, logo, siteName, homeNextLink, router }) => (
  <Responsive getWidth={getWidth} minWidth={Responsive.onlyTablet.minWidth}>
    <Menu
      fixed="top"
      pointing
      secondary
      size="large"
    >
      <Menu.Item>
        {logo.url && (
          <Link {...homeNextLink}>
            <Image size="mini" src={logo.url} alt={logo.alt} title={siteName} href="/" />
          </Link>
        )}
      </Menu.Item>
      <Container>
        {menu.map(link => (
          <Link {...link.nextLink} key={link.nextLink.url}>
            <Menu.Item active={link.nextLink.as === router.asPath}>
              {link.label}
            </Menu.Item>
          </Link>
        ))}
      </Container>
    </Menu>
  </Responsive>
);

DesktopNavigation.propTypes = {
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
  router: PropTypes.shape({
    asPath: PropTypes.string,
  }).isRequired,
};

DesktopNavigation.defaultProps = {
  menu: [],
  logo: {},
  siteName: '',
  homeNextLink: null,
};

export default withRouter(DesktopNavigation);
