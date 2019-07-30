import React from 'react';
import Link from '@systemseed/falcon/components/Link';
import PropTypes from 'prop-types';

const HeaderMenu = ({ menu }) => (
  <nav>
    <ul>
      <ul>
        {menu.map(link => (
          <li key={link.nextLink.url}>
            <Link {...link.nextLink}>
              <a>{link.label}</a>
            </Link>
          </li>
        ))}
      </ul>
    </ul>
  </nav>
);

HeaderMenu.propTypes = {
  menu: PropTypes.arrayOf(PropTypes.shape({
    label: PropTypes.string,
    nextLink: PropTypes.shape({
      url: PropTypes.string,
      href: PropTypes.string,
      as: PropTypes.string,
    }),
  })),
};

HeaderMenu.defaultProps = {
  menu: [],
};

export default HeaderMenu;
