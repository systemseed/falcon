import { Selector, t } from 'testcafe';

export default class Page {
  /**
   * Waits until page is loaded on frontend.
   */
  async pageLoaded() {
    await t.expect(Selector('#nprogress').exists).notOk('Loading completed.');
  }
}
