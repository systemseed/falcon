import { Selector, RequestLogger } from 'testcafe';
import * as config from '../utils/config';

// Enable logging of all get requests during while running this fixture.
const logger = RequestLogger({ method: 'get' });

fixture('Logo')
  .page(config.frontendURL)
  .httpAuth(config.httpAuth)
  .beforeEach(config.beforeEach)
  .requestHooks(logger);

test('Logo is visible', async t => {
  const logo = Selector('a.falcon-logo > img');
  await t.expect(logo.exists).ok('The site logo is visible.');
  const logoUrl = await logo.getAttribute('src');

  // Check that actual image file was successfully loaded during page rendering.
  await t.expect(logger.contains(
    record => record.request.url === logoUrl && record.response.statusCode === 200)
  ).ok();
});

