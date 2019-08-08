import React from 'react';
import PropTypes from 'prop-types';

const LandingPage = ({ entity }) => (
  <div>
    {entity && entity.title[0].value}
  </div>
);

LandingPage.defaultProps = {
  entity: '',
};

LandingPage.propTypes = {
  entity: PropTypes.shape({
    title: PropTypes.array,
  }),
};

export default LandingPage;
