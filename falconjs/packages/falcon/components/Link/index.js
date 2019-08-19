import React from 'react';
import PropTypes from 'prop-types';
import Link from 'next/link';

/**
 * A little proxy around Next.js <Link> component
 * to make sure that only the correct props passed to <Link> component.
 */
const FalconLink = (props) => {
  const linkProps = {};
  const allowedProps = ['href', 'as', 'prefetch', 'replace', 'shallow', 'passHref', 'scroll', 'children'];

  // Put into link properties only values which are allowed for <Link> component.
  // eslint-disable-next-line no-restricted-syntax
  for (const key in props) {
    // eslint-disable-next-line no-prototype-builtins
    if (props.hasOwnProperty(key) && allowedProps.includes(key)) {
      linkProps[key] = props[key];
    }
  }

  return <Link {...linkProps} />;
};

FalconLink.propTypes = {
  href: PropTypes.oneOfType([PropTypes.string, PropTypes.object]).isRequired,
  as: PropTypes.oneOfType([PropTypes.string, PropTypes.object]),
  prefetch: PropTypes.bool,
  replace: PropTypes.bool,
  shallow: PropTypes.bool,
  passHref: PropTypes.bool,
  scroll: PropTypes.bool,
  children: PropTypes.oneOfType([PropTypes.element]).isRequired,
};

FalconLink.defaultProps = {
  as: null,
  prefetch: false,
  replace: false,
  shallow: false,
  passHref: false,
  scroll: true,
};

export default FalconLink;
