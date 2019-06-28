<?php

namespace Drupal\falcon_donation\EventSubscriber;

use Drupal\mailsystem\MailsystemManager;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class FalconDonationOrderCompletedSubscriber.
 *
 * @package Drupal\falcon_donation\EventSubscriber
 */
class FalconDonationOrderCompletedSubscriber implements EventSubscriberInterface {

  /**
   * The mail system manager.
   *
   * @var \Drupal\mailsystem\MailsystemManager
   */
  protected $pluginManagerMail;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructor.
   *
   * @param \Drupal\mailsystem\MailsystemManager $plugin_manager_mail
   *   The plugin manager mail.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger instance.
   */
  public function __construct(MailsystemManager $plugin_manager_mail, LoggerInterface $logger) {
    $this->pluginManagerMail = $plugin_manager_mail;
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
   * Sending thank you email when order payment completed.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   Order completed event.
   */
  public function orderCompleteHandler(WorkflowTransitionEvent $event) {
    try {
      /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
      $order = $event->getEntity();
      if ($order->bundle() !== 'donation') {
        return;
      }

      if ($order->get('field_appeal')->isEmpty()) {
        $this->logger
          ->error(
            'Could not send Thank You email, because the donation order does not have reference to the appeal. Related order ID is @id.',
            ['@id' => $order->id()]
          );
        return;
      }

      $appeal = $order->get('field_appeal')->entity;

      $subject = $appeal->get('field_thankyou_email_subject')->value;
      $body = $appeal->get('field_thankyou_email_body')->value;

      // Check subject and body for thank you email.
      if (empty($subject) || empty($body)) {
        $this->logger
          ->error(
            'Could not send Thank You email, because the Thank You email subject or body is empty. Related appeal ID is @appeal_id, related order ID is @order_id.',
            ['@appeal_id' => $appeal->id(), '@order_id' => $order->id()]
          );
        return;
      }

      $to = $order->getEmail();

      // TODO: add multilingual support.
      $langcode = \Drupal::languageManager()->getDefaultLanguage()->getId();

      $params = [];
      $params['subject'] = $subject;
      $params['body'] = $body;
      $params['headers'] = ['Content-Type' => 'text/html'];
      $params['render_tokens']['commerce_order'] = $order;
      $params['replace_tokens'] = TRUE;

      $this->pluginManagerMail->mail('falcon_donation', 'donation_thank_you_email', $to, $langcode, $params);
    }
    catch (\Exception $e) {
      $this->logger
        ->alert(
          'Could not send Thank You email when order payment completed. Error: @error',
          ['@error' => $e->getMessage()]
        );
    }
  }

}
