import { Selector, ClientFunction } from 'testcafe';
import * as config from '../utils/config';
import Page from '../utils/frontend-model';

fixture('Navigation')
  .page(config.frontendURL).httpAuth(config.httpAuth).beforeEach(config.beforeEach);

const page = new Page();

test('Top menu links work without errors', async t => {
  const menu = Selector('.top-navigation');
  await t.expect(menu.exists).ok('Top navigation menu is visible.');

  const menuItems = Selector('.top-navigation a.item');
  const count = await menuItems.count;

  const expectedMenuUrls = [
    '/',
    '/about',
    '/frontend-only'
  ];

  await t
    .expect(count)
    .eql(expectedMenuUrls.length, 'Top menu contains correct number of links.');

  // Visit every link from the header and ensure it has correct URL and
  // does not return an error page.
  for (let i = 0; i < count; i++) {
    await t.click(menuItems.nth(i));
    await page.pageLoaded();
    const getPathname = ClientFunction(() => window.location.pathname); // eslint-disable-line no-undef
    await t.expect(await getPathname()).eql(expectedMenuUrls[i], `Current pathname = ${expectedMenuUrls[i]}`);
    await t.expect(Selector('.error-page').exists).notOk('No 40x/50x error on the page.');
  }
});

