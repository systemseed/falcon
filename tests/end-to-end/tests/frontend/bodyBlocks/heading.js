import { Selector } from 'testcafe';
import * as config from '../../utils/config';

fixture('Body blocks')
  .page(config.frontendURL).httpAuth(config.httpAuth).beforeEach(config.beforeEach);

test('Heading BB on home page is visible', async t => {
  const headingBB = Selector('.bb.bb-heading h3').withText('The world’s best Charity CMS.');
  await t.expect(headingBB.exists).ok('Heading BB is visible.');
});

