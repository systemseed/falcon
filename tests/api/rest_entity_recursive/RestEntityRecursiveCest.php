<<<<<<< HEAD
=======
<?php

namespace rest_entity_recursive;

/**
 * Class RestEntityRecursiveCest.
 *
 * @package Rest
 */
class RestEntityRecursiveCest {

  private $category;

  private $article;

  private $entityTypeManager;

  private $redirect;

  /**
   * Configuring DB before run test.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function _before() {
    $this->entityTypeManager = \Drupal::entityTypeManager();

    // Creating category.
    $this->category = $this->entityTypeManager->getStorage('taxonomy_term')->create([
      'uid' => 1,
      'vid' => 'content_categories',
      'name' => 'Test category',
    ]);
    $this->category->save();

    // Creating article.
    $this->article = $this->entityTypeManager->getStorage('node')->create([
      'uid' => 1,
      'type' => 'news',
      'title' => 'Test news',
      'status' => 1,
      'field_content_category' => $this->category,
      'path' => [['alias' => '/test-node']],
      'language' => 'und'
    ]);
    $this->article->save();

    $this->redirect = $this->entityTypeManager->getStorage('redirect')->create([
      'redirect_source' => 'test-redirect',
      'redirect_redirect' => 'internal:/test-node',
      'language' => 'und',
      'status_code' => '301',
    ]);
    $this->redirect->save();

  }

  /**
   * Delete created entities and return default config settings.
   */
  public function _after() {
    $this->entityTypeManager->getStorage('node')->delete([$this->article]);
    $this->entityTypeManager->getStorage('taxonomy_term')->delete([$this->category]);
    $this->entityTypeManager->getStorage('redirect')->delete([$this->redirect]);

  }

  /**
   * Checks location in redirect response.
   *
   * @param \ApiTester $I   * @group additional
   */
  public function testRedirectJsonRecursiveFormat(\ApiTester $I) {
    // There is a ghost bug with infinite redirect immediately after
    // an article is created in _before() method. Flush all caches to make sure
    // Drupal is ready to handle requests.
    drupal_flush_all_caches();

    $I->amGoingTo('Get request with redirect to REST endpoint with json_recursive format and check location in response.');
    $I->haveHttpHeader('Content-Type', 'application/json');

    $I->stopFollowingRedirects();

    $I->sendGET('/test-redirect?_format=json_recursive');

    $I->seeResponseCodeIs(301);

    $location = $I->grabHttpHeader('Location');
    $I->assertContains('/test-node', $location);

  }

  /**
   * Checks reference entity not in response via default REST endpoint.
   *
   * @param \ApiTester $I
   * @group additional
   */
  public function testJsonFormat(\ApiTester $I) {
    $I->amGoingTo('Get request to REST endpoint with json format and check exists reference entity fields in response');
    $I->haveHttpHeader('Content-Type', 'application/json');

    $I->sendGET('/test-node?_format=json');

    $I->seeResponseCodeIs(200);

    $I->expectTo('Don`t see category name in response.');
    $I->dontSeeResponseContainsJson(['name' => [['value' => 'Test category']]]);

  }

  /**
   * Checks reference entity in response via default REST endpoint with json_responsive format.
   *
   * @param \ApiTester $I
   * @group additional
   */
  public function testJsonRecursiveFormat(\ApiTester $I) {
    $I->amGoingTo('Get request to REST endpoint with json_recursive format and check exists reference entity fields in response');
    $I->haveHttpHeader('Content-Type', 'application/json');

    $I->sendGET('/test-node?_format=json_recursive');

    $I->expectTo('See category name in response.');
    $I->seeResponseContainsJson(['name' => [['value' => 'Test category']]]);

  }

}
>>>>>>> Added redirect test (first).
