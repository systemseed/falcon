const he = require('he');
const getEntityURL = require('./getEntityURL');
const { BACKEND_URL } = require('./constants');

/**
 * Returns single object item.
 */
const getObjectValue = (entity, fieldName) => {
  if (!entity || typeof entity === 'undefined') {
    return null;
  }

  if (typeof entity[fieldName] !== 'object') {
    return null;
  }

  if (Array.isArray(entity[fieldName]) && typeof entity[fieldName][0] !== 'object') {
    return null;
  }

  return entity[fieldName][0] || entity[fieldName] || null;
};

/**
 * Returns array items.
 */
const getArrayValue = (entity, fieldName) => {
  if (!entity || typeof entity === 'undefined') {
    return [];
  }

  if (typeof entity[fieldName] !== 'object') {
    return [];
  }

  return entity[fieldName];
};

/**
 * Get filtered / plain text value from the field
 */
const getTextValue = (entity, fieldName) => {
  const field = getObjectValue(entity, fieldName);

  if (!field) {
    return '';
  }

  // If the field has "processed" property it means that it is
  // filtered text and the "processed" property must be used.
  if (field.hasOwnProperty('processed')) {
    return field.processed;
  }

  return field.hasOwnProperty('value') ? he.decode(String(field.value)) : '';
};

/**
 * Get text value from the field as usual and return it as number.
 */
const getNumberValue = (entity, fieldName) => {
  const field = getObjectValue(entity, fieldName);

  if (!field) {
    return 0;
  }

  return field.hasOwnProperty('value') ? Number.parseInt(field.value, 10) : 0;
};

/**
 * Get a bool value from the field as usual and return it.
 */
const getBooleanValue = (entity, fieldName) => {
  const field = getObjectValue(entity, fieldName);

  if (!field) {
    return false;
  }

  return field.hasOwnProperty('value') && field.value === true;
};

/**
 * Returns image field URL to the given image style origin.
 */
const getImageURL = (entity, fieldName, imageStyle = 'original') => {
  const field = getObjectValue(entity, fieldName);

  if (!field) {
    return '';
  }

  if (!field.hasOwnProperty('image_styles')) {
    // Sometimes media image field has multiple nesting of field data. In
    // this case we need to make th
    return field.hasOwnProperty('field_media_image') ? getImageURL(field, 'field_media_image', imageStyle) : '';
  }

  if (!field.image_styles.hasOwnProperty(imageStyle)) {
    return '';
  }

  return BACKEND_URL + field.image_styles[imageStyle];
};

/**
 * Return image field's alt.
 */
const getImageAlt = (entity, fieldName) => {
  const field = getObjectValue(entity, fieldName);

  if (!field) {
    return '';
  }

  if (!field.hasOwnProperty('alt')) {
    // Sometimes media image field has multiple nesting of field data. In
    // this case we need to make th
    return field.hasOwnProperty('field_media_image') ? getImageAlt(field, 'field_media_image') : '';
  }

  return field.alt;
};

/**
 * Returns image object with both url and alt fields in place.
 */
const getImage = (entity, fieldName, imageStyle = 'original') => ({
  url: getImageURL(entity, fieldName, imageStyle),
  alt: getImageAlt(entity, fieldName),
});

/**
 * Return properly file URL from the file field.
 */
const getFileURL = (entity, fieldName) => {
  const file = getObjectValue(entity, fieldName);

  if (!file) {
    return '';
  }

  const uri = getObjectValue(file, 'uri');
  return uri && uri.hasOwnProperty('url') ? BACKEND_URL + uri.url : '';
};

/**
 * Return file mime name of the file.
 * Usually it's something like "application/pdf".
 */
const getFileMime = (entity, fieldName) => {
  const file = getObjectValue(entity, fieldName);

  if (!file) {
    return '';
  }

  return getTextValue(file, 'filemime');
};

/**
 * Return file size value.
 * The value is in bytes.
 */
const getFileSize = (entity, fieldName) => {
  const file = getObjectValue(entity, fieldName);

  if (!file) {
    return 0;
  }

  return getNumberValue(file, 'filesize');
};

const getLinkValue = (entity, fieldName) => {
  const link = getObjectValue(entity, fieldName);

  if (link) {
    return {
      label: link.label,
      nextLink: getEntityURL(link),
    };
  }

  return {
    label: '',
    nextLink: null,
  };
};
// The export is done this way so that node.js can also
// require these functions.
module.exports = {
  getEntityURL,
  getLinkValue,
  getObjectValue,
  getArrayValue,
  getTextValue,
  getNumberValue,
  getBooleanValue,
  getImageURL,
  getImageAlt,
  getImage,
  getFileURL,
  getFileMime,
  getFileSize,
};
