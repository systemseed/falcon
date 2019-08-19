import * as field from '@systemseed/falcon/utils/transforms.fields';

/**
 * Handles each transformation based on blockType index.
 */
export default {
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
