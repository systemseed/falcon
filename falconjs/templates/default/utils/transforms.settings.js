import getHomepageLink from '@systemseed/falcon/utils/getHomepageLink';
import * as field from '@systemseed/falcon/utils/transforms.fields';

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

  const homepageLinkRaw = getHomepageLink(settings, true);
  props.menu = menu.map((menuItem) => {
    let nextLink = field.getEntityURL(menuItem);

    // Replace menu link to homepage link if menu link has url like '/home'.
    // The active menu item should be highlighted, without this replacement
    // the link will lead to "/home". But app.js replaces "/home" url to "/", therefore
    // we need to have same value in the link and in the router.asPath.
    if (homepageLink && homepageLinkRaw && nextLink.url === homepageLinkRaw.url) {
      nextLink = homepageLink;
    }
    return {
      nextLink,
      slug: menuItem.title,
      label: menuItem.hasOwnProperty('title') ? menuItem.title : '',
    };
  });

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
