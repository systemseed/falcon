<?php

namespace Drupal\falcon_donation\Plugin\rest\resource;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Psr\Log\LoggerInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_price\Price;

/**
 * Provides a resource for donations.
 *
 * @RestResource(
 *   id = "donation",
 *   label = @Translation("Falcon Commerce Donation"),
 *   uri_paths = {
 *     "create" = "/falcon/donation"
 *   }
 * )
 */
class DonationResource extends ResourceBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected $time;

  /**
   * An array of data given from front end.
   *
   * @var array
   */
  private $data;

  /**
   * User account entity.
   *
   * @var \Drupal\user\UserInterface
   */
  private $account;

  /**
   * Profile entity.
   *
   * @var \Drupal\profile\Entity\ProfileInterface
   */
  private $profile;

  const VARIATION_SKU = 'donation';
  const ORDER_ITEM_TYPE = 'donation';
  const ORDER_TYPE = 'donation';

  /**
   * Constructs a Drupal\falcon_donation\Plugin\rest\resource\DonationResource object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param array $serializer_formats
   *   The available serialization formats.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, array $serializer_formats, LoggerInterface $logger, EntityTypeManagerInterface $entity_type_manager, TimeInterface $time) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $serializer_formats, $logger);
    $this->entityTypeManager = $entity_type_manager;
    $this->time = $time;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->getParameter('serializer.formats'),
      $container->get('logger.factory')->get('falcon_donation'),
      $container->get('entity_type.manager'),
      $container->get('datetime.time')
    );
  }

  /**
   * Handle POST requests.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Request object.
   *
   * @return \Drupal\rest\ResourceResponse
   *   Response object.
   */
  public function post(Request $request) {
    // TODO: check if we can get this data some other way provided by REST.
    // Look into the serialization_class.
    $this->data = json_decode($request->getContent(), TRUE);

    $order = $this->createOrder();
    $this->processPayment($order);

    $response = new ResourceResponse($order, 200);

    return $response;
  }

  /**
   * Creates an order from given data.
   */
  protected function createOrder() {
    // Find out the product variation.
    /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $variation */
    $variations = $this->entityTypeManager->getStorage('commerce_product_variation')->loadByProperties(['sku' => self::VARIATION_SKU]);
    $variation = reset($variations);

    // Get store ID from the product variation.
    $product = $variation->getProduct();
    $store_ids = $product->getStoreIds();

    // Create order item object with found product variation.
    /** @var \Drupal\commerce_order\Entity\OrderItemInterface $order_item */
    $order_item = $this->entityTypeManager->getStorage('commerce_order_item')->create([
      'title' => ucfirst(str_replace('_', ' ', $this->data['donation_type'])),
      'type' => self::ORDER_ITEM_TYPE,
      'purchased_entity' => $variation,
      'quantity' => 1,
      'field_donation_type' => $this->data['donation_type'],
    ]);

    // Set price.
    $unit_price = new Price($this->data['payment']['amount'], $this->data['payment']['currency_code']);
    $order_item->setUnitPrice($unit_price, TRUE);

    // Validate and save order item.
    try {
      // We need only entity level violations.
      /** @var \Drupal\Core\Entity\EntityConstraintViolationListInterface $violations */
      $violations = $order_item->validate()->getEntityViolations();
      if ($violations->count() > 0) {
        foreach ($violations as $violation) {
          $this->logger->warning($violation->getPropertyPath() ?: 'Entity' . ': ' . $violation->getMessage());
        }

        throw new \Exception('Order item violations.');
      }
      $order_item->save();
    }
    catch (\Exception $e) {
      watchdog_exception('falcon_donation', $e);
      throw new HttpException(500, 'Internal Server Error', $e);
    }

    // Get user and profile objects.
    $account = $this->getUser();
    $profile = $this->getProfile();

    // Create an order with created order item.
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $this->entityTypeManager->getStorage('commerce_order')->create(array_merge($this->data['order'],
      [
        'type' => self::ORDER_TYPE,
        'state' => 'draft',
        'mail' => $account->getEmail(),
        'uid' => $account->id(),
        'billing_profile' => $profile,
        'store_id' => reset($store_ids),
        'order_items' => [$order_item],
        'placed' => $this->time->getRequestTime(),
      ])
    );

    // Validate and save order.
    try {
      // We need only entity level violations.
      /** @var \Drupal\Core\Entity\EntityConstraintViolationListInterface $violations */
      $violations = $order->validate()->getEntityViolations();
      if ($violations->count() > 0) {
        foreach ($violations as $violation) {
          $this->logger->warning($violation->getPropertyPath() ?: 'Entity' . ': ' . $violation->getMessage());
        }

        throw new \Exception('Order violations.');
      }
      $order->save();
    }
    catch (\Exception $e) {
      watchdog_exception('falcon_donation', $e);
      throw new HttpException(500, 'Internal Server Error', $e);
    }

    $order->setOrderNumber($order->id());
    $order->save();
    return $order;
  }

  /**
   * Process payment.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   Order.
   */
  protected function processPayment(OrderInterface $order) {
    // TODO: add payment processing.

    // Complete the order.
    $order_state = $order->getState();
    $order_state_transitions = $order_state->getTransitions();
    $order_state->applyTransition($order_state_transitions['place']);
    $order->save();
  }

  /**
   * Create a new profile from given data.
   *
   * @return \Drupal\profile\Entity\ProfileInterface
   *   Profile entity.
   */
  private function getProfile() {
    if (!empty($this->profile)) {
      return $this->profile;
    }

    $user = $this->getUser();
    $profile = $this->entityTypeManager->getStorage('profile')->create(array_merge($this->data['profile'],
      [
        'type' => 'customer',
        'uid' => $user->id(),
      ])
    );

    try {
      $profile->save();
    }
    catch (\Exception $e) {
      watchdog_exception('falcon_donation', $e);
      throw new HttpException(500, 'Internal Server Error', $e);
    }

    $this->profile = $profile;

    return $this->profile;
  }

  /**
   * Load existing or create a new user from given email.
   *
   * @return \Drupal\user\UserInterface
   *   User object.
   */
  private function getUser() {
    if (!empty($this->account)) {
      return $this->account;
    }

    $email = $this->data['profile']['email'];
    if ($account = user_load_by_mail($email)) {
      $this->account = $account;
    }
    else {
      $account = $this->entityTypeManager->getStorage('user')->create(array_merge($this->data['profile'],
        [
          'name' => $email,
          'mail' => $email,
          'status' => 0,
        ])
      );

      try {
        $account->save();
      }
      catch (\Exception $e) {
        watchdog_exception('falcon_donation', $e);
        throw new HttpException(500, 'Internal Server Error', $e);
      }

      $this->account = $account;
    }

    return $this->account;
  }

}
