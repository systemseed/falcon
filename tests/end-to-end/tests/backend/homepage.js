import { Selector } from 'testcafe';
import * as config from '../utils/config';

fixture('Backend home page is working')
  .page(config.backendURL).httpAuth(config.httpAuth).beforeEach(config.beforeEach);

test('Visit homepage', async t => {
  const button = Selector('input.button[value="Log in"]');
  await t.expect(button.exists).ok();
});
