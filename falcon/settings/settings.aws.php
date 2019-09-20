<?php

// Configure private and temporary file paths.
$settings['file_private_path'] = '/private/drupal';
$config['system.file']['path']['temporary'] = '/tmp/drupal';

// Configure the default PhpStorage and Twig template cache directories.
$settings['php_storage']['default']['directory'] = $settings['file_private_path'];
$settings['php_storage']['twig']['directory'] = $settings['file_private_path'];

// Specify that it works behind a reverse proxy.
$settings['reverse_proxy'] = TRUE;
$settings['reverse_proxy_addresses'] = [$_SERVER['REMOTE_ADDR']];

// Enable css / js aggregation.
$config['system.performance']['css']['preprocess'] = TRUE;
$config['system.performance']['js']['preprocess'] = TRUE;

// Adds http creds if exists.
if (
  !empty(str_replace("'", '', $_ENV['HTTP_AUTH_USER']))
  && !empty(str_replace("'", '', $_ENV['HTTP_AUTH_PASS']))
) {
  include $app_root . '/' . $site_path . '/http_auth.php';
}
