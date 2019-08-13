import { Selector, ClientFunction } from 'testcafe';
import * as config from '../utils/config';
import Page from '../utils/frontend-model';

fixture('Navigation')
  .page(config.frontendURL).httpAuth(config.httpAuth).beforeEach(config.beforeEach);

const page = new Page();

test
  ('Top menu links work without errors', async t => {
    const menu = Selector('header div.ui.fixed.menu div.ui.container');
    await t.expect(menu.exists).ok('Get in Touch! Navigation menu visible.');

    const menuItems = Selector('header div.ui.fixed.menu div.ui.container a.item');
    const count = await menuItems.count;

    await t
      .expect(count)
      .eql(3, 'Get in Touch! Navigation menu contains 3 links.');

    const menuUrls = [
      '/',
      '/about',
      '/frontend-only'
    ];

    for (let i = 0; i < count; i++) {
      await t.click(menuItems.nth(i));
      await page.pageLoaded();
      const getPathname = ClientFunction(() => window.location.pathname);
      await t.expect(await getPathname()).eql(menuUrls[i], `Current pathname = ${menuUrls[i]}`, { timeout: 5000 });
      await t.expect(Selector('.page-content .error-page').exists).notOk('No 40x/50x error on the page.');
    }
  });

