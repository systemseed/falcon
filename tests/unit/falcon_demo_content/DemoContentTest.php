<?php

namespace falcon_demo_content;

class DemoContentTest extends \Codeception\Test\Unit {

  const DEMO_CONTENT_MODULE = 'falcon_demo_content';

  /**
   * Checks for the presence demo content.
   * @group demo-content
   */
  public function testDemoContentEnabled()
  {
    $this->assertTrue(
      \Drupal::moduleHandler()->moduleExists(self::DEMO_CONTENT_MODULE),
      "Module " . self::DEMO_CONTENT_MODULE . " should be enabled."
    );

    // Get array of demo content entities uuid and type.
    $contentEntities = $this->getContentEntities();
    $entityRepository = \Drupal::service('entity.repository');

    // Checks for the presence entity in database.
    foreach ($contentEntities as $contentEntity) {
      $this->assertNotEmpty($entityRepository->loadEntityByUuid($contentEntity['entity_type_id'], $contentEntity['uuid']),
        "Missing content with uuid = " . $contentEntity['uuid']);
    }
  }

  /**
   * Checks for the missing demo content.
   * @group demo-content
   */
  public function testDemoContentDisabled()
  {
    $this->assertTrue(
      \Drupal::moduleHandler()->moduleExists(self::DEMO_CONTENT_MODULE),
      "Module " . self::DEMO_CONTENT_MODULE . " should be enabled."
    );

    // Get array of demo content entities uuid and type.
    $contentEntities = $this->getContentEntities();

    // Disable demo content module.
    \Drupal::service('module_installer')->uninstall([self::DEMO_CONTENT_MODULE]);

    $this->assertFalse(
      \Drupal::moduleHandler()->moduleExists(self::DEMO_CONTENT_MODULE),
      "Module " . self::DEMO_CONTENT_MODULE . " should be uninstalled."
    );

    $entityRepository = \Drupal::service('entity.repository');
    // Checks for the missing entity in database.
    foreach ($contentEntities as $contentEntity) {
      $this->assertEmpty($entityRepository->loadEntityByUuid($contentEntity['entity_type_id'], $contentEntity['uuid']),
        "Exists demo content entity with uuid = " . $contentEntity['uuid']);
    }

    // Enable demo content module.
    // TODO: Fix bug with enable default content;
    //\Drupal::service('module_installer')->install([self::DEMO_CONTENT_MODULE]);
  }

  /**
   * Get type and uuid from all content files from demo content module.
   *
   * @return array
   */
  private function getContentEntities() {
    $this->assertTrue(
      \Drupal::moduleHandler()->moduleExists('default_content'),
      "Module default_content should be enabled."
    );

    $scanner = \Drupal::service('default_content.scanner');
    $serializer = \Drupal::service('serializer');

    $folder = drupal_get_path('module', self::DEMO_CONTENT_MODULE) . "/content";
    // Get all files from folder.
    $files = $scanner->scan($folder);

    $result = [];
    foreach ($files as $url => $file) {
      $data = file_get_contents($file->uri);
      // Decode the file contents.
      $decoded = $serializer->decode($data, 'hal_json');
      // Get the link to this entity.
      $uuid = $decoded['uuid'][0]['value'];

      $result[] = ['uuid' => $uuid, 'entity_type_id' => basename(dirname($url))];
    }

    return $result;
  }

}
