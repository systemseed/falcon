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

  private $restResourceConfig;

  private $liveRestResourceConfig;

  /**
   * Configuring DB before run test.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function _before() {
    $this->entityTypeManager = \Drupal::entityTypeManager();
    $this->restResourceConfig = \Drupal::service('config.factory')->getEditable('rest.resource.entity.node');

    // Saving data that will be changed.
    $this->liveRestResourceConfig = [
      'status' => $this->restResourceConfig->get('status'),
      'configuration' => $this->restResourceConfig->get('configuration'),
    ];

    // Saving test data to config.
    $testConfiguration = [
      'methods' => ['GET'],
      'formats' => ['json_recursive'],
      'authentication' => ['cookie']
    ];
    $this->restResourceConfig->set('status', true);
    $this->restResourceConfig->set('configuration', $testConfiguration);
    $this->restResourceConfig->save();

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

  }

  /**
   * Delete created entities and return default config settings.
   */
  public function _after() {
    $this->entityTypeManager->getStorage('node')->delete([$this->article]);
    $this->entityTypeManager->getStorage('taxonomy_term')->delete([$this->category]);
    $this->restResourceConfig->set('status', $this->liveRestResourceConfig['status']);
    $this->restResourceConfig->set('configuration', $this->liveRestResourceConfig['configuration']);
    $this->restResourceConfig->save();
  }

  /**
   * Checks reference entity in response via default REST endpoint.
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
