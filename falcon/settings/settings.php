<?php

// @codingStandardsIgnoreFile

/**
 * Default Drupal settings.
 */
$settings['update_free_access'] = FALSE;
$settings['entity_update_batch_size'] = 50;
$settings['container_yamls'][] = $app_root . '/' . $site_path . '/services.yml';
$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];

$settings['hash_salt'] = 'FBG5lyz4cr0zqZLGbCC8wYRmQq1D4kCzMmJ8Kve5r5aKO1FyGHMOg2ob8-f74rUItctAsRhRtA';

$databases['default']['default'] = [
  'database' => $_ENV['DB_NAME'],
  'username' => $_ENV['DB_USER'],
  'password' => $_ENV['DB_PASSWORD'],
  'host' => $_ENV['DB_HOST'],
  'port' => '3306',
  'driver' => 'mysql',
  'prefix' => '',
  'collation' => 'utf8mb4_general_ci',
];

// Settings for AWS environments.
if (!empty($_ENV['AWS_ENVIRONMENT'])) {
  require_once __DIR__ . '/settings.aws.php';
}
