import getHomepageLink from '@systemseed/falcon/utils/getHomepageLink';
import * as field from './transforms.fields';

/**
 * Transforms site wide header settings (menus, logo, etc)
 * into Styleguide-readable format.
 */
export const header = (settings) => {
  const props = {};
  props.siteName = field.getTextValue(settings, 'field_site_name');

  // Link to the front page of the website.
  const homepageLink = getHomepageLink(settings);
  if (homepageLink && homepageLink.href) {
    props.homeNextLink = homepageLink;
  }

  props.logo = field.getImage(settings, 'field_media_logo');

  const fieldMenu = field.getObjectValue(settings, 'field_menu');

  const menu = field.getArrayValue(fieldMenu, 'links');

  props.menu = menu.map(menuItem => ({
    slug: menuItem.title,
    nextLink: field.getEntityURL(menuItem),
    label: menuItem.hasOwnProperty('title') ? menuItem.title : '',
  }));

  return props;
};

/**
 * Transforms site wide footer settings (menus, social links, etc)
 * into Styleguide-readable format.
 */
export const footer = (settings) => {
  const props = {};
  // Footer copy message at the end of the page.
  props.text = field.getTextValue(settings, 'field_footer_text');
  return props;
};
