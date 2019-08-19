import * as field from './transforms.fields';

const getBlockType = block => field.getTextValue(block, 'entity_bundle');

module.exports = (entity, blocks, transformList) => {
  const availableBlocks = Object.keys(transformList).map(blockType => blockType);

  return blocks.map((block) => {
    // Get paragraph bundle name.
    const blockType = getBlockType(block);

    if (availableBlocks.includes(blockType)) {
      const props = transformList[blockType](block, entity);

      if (props) {
        return {
          // blockType MUST be before ...props to allow blockType override
          // in certain cases from the block transform function.
          blockType,
          ...props,
          uuid: field.getTextValue(block, 'uuid'),
        };
      }

      return null;
    }

    return {};
  });
};
