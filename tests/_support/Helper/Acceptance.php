<?php
namespace Helper;

class Acceptance extends \Codeception\Module
{
  /**
   * Returns password for test backend users.
   *
   * @return array|false|string
   */
  public function getTestPassword()
  {
    return getenv('TEST_USERS_PASSWORD');
  }

  /**
   * Returns backend URL.
   *
   * @return array|mixed|null
   * @throws \Codeception\Exception\ModuleException
   */
  public function getBackendURL()
  {
    return  $this->getModule('WebDriver')->_getConfig('backend_url');
  }

  /**
   * Returns frontend URL.
   *
   * @return array|mixed|null
   * @throws \Codeception\Exception\ModuleException
   */
  public function getFrontendURL()
  {
    return  $this->getModule('WebDriver')->_getConfig('url');
  }

  /**
   * Checks if the environment is production.
   *
   * @return boolean
   *   TRUE or FALSE.
   */
  public function isProduction() {
    return getenv('ENVIRONMENT') && getenv('ENVIRONMENT') === 'production';
  }

}
