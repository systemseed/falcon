<?php

namespace Drupal\falcon_gift_ecards;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\mailsystem\MailsystemManager;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Config\ConfigFactory;
use Psr\Log\LoggerInterface;

/**
 * Class MailSender.
 *
 * @package Drupal\falcon_gift_ecards
 */
class MailSender implements MailSenderInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Page config entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $pageConfigStorage;

  /**
   * Drupal\mailsystem\MailsystemManager definition.
   *
   * @var \Drupal\mailsystem\MailsystemManager
   */
  protected $pluginManagerMail;

  /**
   * Drupal\Core\Language\LanguageManager definition.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * Drupal\Core\Config\ConfigFactory definition.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * MailSender constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\mailsystem\MailsystemManager $plugin_manager_mail
   *   MailsystemManager service.
   * @param \Drupal\Core\Language\LanguageManager $language_manager
   *   Landuage service.
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   Config factory service.
   * @param \Psr\Log\LoggerInterface $logger
   *   Module's logger.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, MailsystemManager $plugin_manager_mail, LanguageManager $language_manager, ConfigFactory $config_factory, LoggerInterface $logger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->pluginManagerMail = $plugin_manager_mail;
    $this->languageManager = $language_manager;
    $this->configFactory = $config_factory;
    $this->logger = $logger;
  }

  /**
   * Send email with gift link to a friend.
   *
   * @return bool
   *   TRUE if success.
   */
  public function send($ecardItem) {

    if ($ecardItem->bundle() != 'ecard') {
      return FALSE;
    }

    $to = $ecardItem->get('field_recipient_email')->value;

    if (empty($to)) {
      return FALSE;
    }
    $orderItemId = $ecardItem->get('field_order_item')->getValue();

    if (empty($orderItemId)) {
      return FALSE;
    }
    $orderItemId = $orderItemId[0]['target_id'];

    $orderItem = $this->entityTypeManager
      ->getStorage('commerce_order_item')
      ->load($orderItemId);

    if (empty($orderItem)) {
      return FALSE;
    }

    $order = $orderItem->getOrder();

    if (empty($order) || $order->getState()->getOriginalId() !== 'completed') {
      return FALSE;
    }

    $params = [
      'headers' => [
        'Content-Type' => 'text/html',
      ],
      'from' => $this->configFactory->get('system.site')->get('mail'),
      'subject' => $ecardItem->get('field_subject')->getValue()[0]['value'],
      'body' => $ecardItem->get('field_message')->getValue()[0]['value'],
    ];

    $langcode = $this->languageManager->getDefaultLanguage()->getId();

    $message = $this->pluginManagerMail->mail('falcon_gift_ecards', 'ecard_mail', $to, $langcode, $params, NULL, TRUE);
    if ($message['result']) {
      $ecardItem->set('field_status', TRUE);
      $ecardItem->set('field_sent_timestamp', \Drupal::time()->getRequestTime());
      $ecardItem->save();

      $this->logger->info('Gift E-Card @id has been sent to @mail.', ['@id' => $ecardItem->id(), '@mail' => $to]);

      return TRUE;
    }

    return FALSE;
  }

}
