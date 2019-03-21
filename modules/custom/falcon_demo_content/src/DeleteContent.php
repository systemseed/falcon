<?php

namespace Drupal\falcon_demo_content;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\default_content\ScannerInterface;
use Symfony\Component\Serializer\Serializer;

/**
 * Class DeleteContent.
 *
 * @package Drupal\falcon_demo_content
 */
class DeleteContent {

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The serializer service.
   *
   * @var \Symfony\Component\Serializer\Serializer
   */
  protected $serializer;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The file system scanner.
   *
   * @var \Drupal\default_content\ScannerInterface
   */
  protected $scanner;

  /**
   * Constructs.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   Entity repository.
   * @param \Symfony\Component\Serializer\Serializer $serializer
   *   Serializer.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\default_content\ScannerInterface $scanner
   *   Scanner.
   */
  public function __construct(EntityRepositoryInterface $entity_repository, Serializer $serializer, EntityTypeManagerInterface $entity_type_manager, ScannerInterface $scanner) {
    $this->entityRepository = $entity_repository;
    $this->entityTypeManager = $entity_type_manager;
    $this->serializer = $serializer;
    $this->scanner = $scanner;
  }

  /**
   * Remove content that added with default_content module.
   *
   * @param string $module_name
   *   Module name that default content should remove.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \ReflectionException
   */
  public function deleteContent($module_name) {
    $folder = drupal_get_path('module', $module_name) . "/content";
    if (file_exists($folder)) {

      foreach ($this->entityTypeManager->getDefinitions() as $entity_type_id => $entity_type) {
        $reflection = new \ReflectionClass($entity_type->getClass());

        // We are only interested in importing content entities.
        if ($reflection->implementsInterface('\Drupal\Core\Config\Entity\ConfigEntityInterface')) {
          continue;
        }
        if (!file_exists($folder . '/' . $entity_type_id)) {
          continue;
        }
        $files = $this->scanner->scan($folder . '/' . $entity_type_id);

        // Parse all of the files and delete them from DB.
        foreach ($files as $file) {
          $contents = $this->parseFile($file);
          // Decode the file contents.
          $decoded = $this->serializer->decode($contents, 'hal_json');
          // Get the link to this entity.
          $uuid = $decoded['uuid'][0]['value'];
          $entity = $this->entityRepository->loadEntityByUuid($entity_type_id, $uuid);

          if (!empty($entity)) {
            $entity->delete();
          }
        }
      }
    }
  }

  /**
   * Parses content files.
   *
   * @param object $file
   *   The scanned file.
   *
   * @return string
   *   Contents of the file.
   */
  protected function parseFile($file) {
    return file_get_contents($file->uri);
  }

}
