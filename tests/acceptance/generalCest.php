<?php

class GeneralCest {

  /**
   * Check that front page is accessible from test suite.
   *
   * @group basic
   *
   * @param \AcceptanceTester $I
   */
  public function viewFrontend(AcceptanceTester $I)
  {
    $I->wantTo('make sure the home page is working');
    $I->amGoingTo('view the front page');
    $I->amOnPage('/');
  }

}
