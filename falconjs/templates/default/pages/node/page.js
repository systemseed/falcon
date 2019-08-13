import React from 'react';
import PropTypes from 'prop-types';
import Paragraphs from '../../components/Paragraphs';

const LandingPage = ({ entity, blocks }) => (
  <div>
    <Paragraphs blocks={blocks} entity={entity} />
  </div>
);

LandingPage.defaultProps = {
  entity: '',
  blocks: [],
};

LandingPage.propTypes = {
  entity: PropTypes.shape({
    title: PropTypes.array,
  }),
  blocks: PropTypes.arrayOf([PropTypes.shape]),
};

export default LandingPage;
