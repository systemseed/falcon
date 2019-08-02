import { Responsive } from 'semantic-ui-react';

/**
 * We using React Static to prerender our docs with server side rendering,
 * this is a quite simple solution.
 * For more advanced usage please check Responsive docs under the "Usage" section.
 * https://react.semantic-ui.com/addons/responsive/
 */
const getWidth = () => {
  const isSSR = typeof window === 'undefined';

  return isSSR ? Responsive.onlyTablet.minWidth : window.innerWidth;
};

export default getWidth;
