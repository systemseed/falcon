import { Selector } from 'testcafe';
import * as config from '../utils/config';
import { getRequestResult } from '../utils/helpers';

fixture('Logo')
  .page(config.frontendURL).httpAuth(config.httpAuth).beforeEach(config.beforeEach);

test
  ('Logo is visible', async t => {
    const logo = Selector('a.falcon-logo > img');
    await t.expect(logo.exists).ok('Get in Touch! Logo is visible.');
    const logoUrl = await logo.getAttribute('src');

    await t.expect(getRequestResult(logoUrl)).eql(200, 'Logo is loaded.');
  });

