import * as field from './transforms.fields';

const getBlockType = block => field.getTextValue(block, 'entity_bundle');

export default class TransformBlocks {
  /**
   * Handles each transformation based on blockType index.
   */
  getProps = {
    /**
     * Handles "Heading" body block.
     */
    heading: block => ({
      title: field.getTextValue(block, 'field_heading'),
      Tag: field.getTextValue(block, 'field_heading_size'),
    }),

    /**
     * Handles "Text" body block.
     */
    text: block => ({
      text: field.getTextValue(block, 'field_text'),
    }),

    /**
     * Handles "Image" body block.
     */
    image: (block) => {
      const image = field.getObjectValue(block, 'field_media_image');

      if (!image) {
        return null;
      }

      return {
        image: field.getImage(image, 'field_media_image', 'full_size'),
      };
    },
  };

  transform(entity, blocks) {
    const availableBlocks = Object.keys(this.getProps).map(blockType => blockType);

    return blocks.map((block) => {
      // Get paragraph bundle name.
      const blockType = getBlockType(block);

      if (availableBlocks.includes(blockType)) {
        const props = this.getProps[blockType](block, entity);

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
  }
}
