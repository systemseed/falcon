<?php

namespace Drupal\falcon_gift_ecards\EventSubscriber;

use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\falcon_gift_ecards\MailSender;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class FalconOrderCompletedSubscriber.
 *
 * @package Drupal\falcon_gift_ecards\EventSubscriber
 */
class FalconOrderCompletedSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Mail sender.
   *
   * @var \Drupal\falcon_gift_ecards\MailSender
   */
  protected $mailSender;

  /**
   * A storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructor.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, MailSender $mail_sender, LoggerInterface $logger) {
    $this->mailSender = $mail_sender;
    $this->entityTypeManager = $entity_type_manager;
    $this->storage = $entity_type_manager->getStorage('gift_cards');
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      'commerce_order.place.post_transition' => 'orderCompleteHandler',
    ];
    return $events;
  }

  /**
   * Sending ecards when order payment completed.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   Order completed event.
   */
  public function orderCompleteHandler(WorkflowTransitionEvent $event) {
    try {
      /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
      $order = $event->getEntity();

      // Order items in the cart.
      $items = $order->getItems();

      foreach ($items as $item) {
        if ($item->hasField('field_card_delivery')) {
          $card_delivery = $item->get('field_card_delivery')->getValue();
          if (!empty($card_delivery) && $card_delivery[0]['value'] === 'email') {
            $field_cards = $item->get('field_card')->getValue();

            // Send ecards for completed order.
            foreach ($field_cards as $field_card) {
              $card = $this->storage->load($field_card['target_id']);
              if (!empty($card) &&
                !empty($card->get('field_status')->getValue()) &&
                !$card->get('field_status')->getValue()[0]['value']) {

                $this->mailSender->send($card);
              }
            }

          }
        }
      }
    }
    catch (\Exception $e) {
      $this->logger
        ->alert(
          'Can`t send card when order payment completed. Error: @error',
          ['@error' => $e->getMessage()]
        );
    }
  }

}
