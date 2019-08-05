import falconGetEntityUrl from '@systemseed/falcon/utils/getEntityURL';
import * as field from '@systemseed/falcon/utils/transforms.fields';
import routes from '../routes';

const getEntityURL = entity => (
  falconGetEntityUrl(entity, routes)
);

/**
 * Get value of link field. It is usually in the format of
 * { url: '', label: '' }
 */
const getLinkValue = (entity, fieldName) => {
  const link = field.getObjectValue(entity, fieldName);

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

module.exports = {
  ...field,
  getEntityURL,
  getLinkValue,
};
