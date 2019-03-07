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
      'path' => [['alias' => '/test-node']]
    ]);
    $this->article->save();
    drupal_flush_all_caches();

  }

  /**
   * Delete created entities and return default config settings.
   */
  public function _after() {
    $this->entityTypeManager->getStorage('node')->delete([$this->article]);
    $this->entityTypeManager->getStorage('taxonomy_term')->delete([$this->category]);
  }

  /**
   * Checks reference entity not in response via default REST endpoint.
   *
   * @param \ApiTester $I
   * @group additional
   */
  public function ReferencesEntitiesNotInResponse(\ApiTester $I) {
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
  public function ReferencesEntitiesInResponse(\ApiTester $I) {
    $I->amGoingTo('Get request to REST endpoint with json_recursive format and check exists reference entity fields in response');
    $I->haveHttpHeader('Content-Type', 'application/json');

    $I->sendGET('/test-node?_format=json_recursive');

    $I->expectTo('See category name in response.');
    $I->seeResponseContainsJson(['name' => [['value' => 'Test category']]]);

  }

}
