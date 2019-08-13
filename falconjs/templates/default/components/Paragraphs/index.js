import React from 'react';
import PropTypes from 'prop-types';
import components from '../BodyBlocks';

const Paragraphs = ({ blocks }) => {
  const output = [];

  blocks.forEach((block) => {
    // Make sure the block is not null.
    if (!block) {
      return null;
    }

    // Make sure the block has required param.
    if (!block.hasOwnProperty('blockType')) {
      return null;
    }

    // Make sure the block has matching component.
    if (typeof components[block.blockType] === 'undefined') {
      return null;
    }
    // Body block matching entity bundle of paragraph.
    const Paragraph = components[block.blockType];

    return output.push(<Paragraph key={block.uuid} {...block} />);
  });

  return output;
};

Paragraphs.propTypes = {
  blocks: PropTypes.arrayOf(PropTypes.shape),
};

Paragraphs.defaultProps = {
  blocks: [],
};

export default Paragraphs;
