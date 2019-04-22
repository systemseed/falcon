<?php

namespace Drupal\falcon_mail\Plugin\Mail;

use Drupal;
use Exception;
use Drupal\Component\Utility\Html;
use Drupal\Core\Mail\Plugin\Mail\PhpMail;

/**
 * Modify the drupal mail system to use theme template and replace tokens when sending emails.
 *
 * @Mail(
 *   id = "falcon_mail_system",
 *   label = @Translation("Falcon Mailer"),
 *   description = @Translation("Provides formatter that can wrap emails into HTML templates and replace tokens.")
 * )
 */
class FalconMailSystem extends PhpMail {

  /**
   * The body token.
   */
  const BODY_TOKEN = '#$#BODY#$#';

  /**
   * Concatenate and wrap the e-mail body for either HTML emails.
   *
   * @param array $message
   *   A message array.
   *
   * @return array
   *   The formatted $message.
   */
  public function format(array $message) {
    $message['subject'] = !empty($message['subject']) ? $message['subject'] :
      (isset($message['params']['subject']) ? $message['params']['subject'] : '');
    $message['body'] = !empty($message['body']) ? $message['body'] :
      (isset($message['params']['body']) ? $message['params']['body'] : []);
    $message['from'] = !empty($message['from']) ? $message['from'] :
      (isset($message['params']['from']) ? $message['params']['from'] : Drupal::config('system.site')->get('mail'));

    // Merge headers.
    if (!empty($message['params']['headers'])) {
      $message['headers'] = array_merge($message['headers'], $message['params']['headers']);
    }

    if (is_array($message['body'])) {
      $message['body'] = implode("\n\n", $message['body']);
    }

    // Wrap body in theme template.
    if (!empty($message['params']['theme_template'])) {
      $message['body'] = str_replace(self::BODY_TOKEN, $message['body'], $message['params']['theme_template']);
    }

    // Replace tokens.
    if (!empty($message['params']['replace_tokens'])) {

      $tokens = !empty($message['params']['render_tokens']) ? $message['params']['render_tokens'] : [];
      $token_options = !empty($message['params']['token_options']) ? $message['params']['token_options'] : [];

      // Replace tokens and set new body.
      $message['body'] = Drupal::token()->replace($message['body'], $tokens, $token_options);
    }

    // Replace relative urls in body.
    $message['body'] = $this->replaceRelativeUrlsWithSaveStyles($message['body']);

    return $message;
  }

  /**
   * Send the e-mail message with using default PHP_Mail.
   *
   * @param array $message
   *   A message array.
   *
   * @return bool
   *   TRUE if the mail was successfully accepted, otherwise FALSE.
   */
  public function mail(array $message) {
    return parent::mail($message);
  }

  /**
   * Replace relative urls on absolute with saving <html>, <body>, <head> tags.
   *
   * @param string $body
   *   Mail body.
   *
   * @return string
   *   String with replaced relative urls.
   */
  public function replaceRelativeUrlsWithSaveStyles($body) {
    try {
      // Create Global dom document.
      $html = new \DOMDocument();
      $html->loadHTML($body);

      // If $html does't have body than return $body with replaced tokens.
      if (empty($html->getElementsByTagName('body')->count())) {
        return Html::transformRootRelativeUrlsToAbsolute((string) $body, \Drupal::request()
          ->getSchemeAndHttpHost());
      }

      // Get body element.
      $bodyElement = $html->getElementsByTagName('body')[0];

      // Get body structure as string.
      $strBody = $html->saveHTML($bodyElement);

      // Replace all relative urls in body.
      $strNewBody = Html::transformRootRelativeUrlsToAbsolute((string) $strBody, \Drupal::request()
        ->getSchemeAndHttpHost());

      // Create new HTML document with new body.
      $newBodyHtml = Html::load($strNewBody);
      // Get new body element.
      $newBodyElement = $newBodyHtml->getElementsByTagName('body')[0];
      // Provide new body element to Global dom document.
      $newBodyElement = $html->importNode($newBodyElement, TRUE);

      // Remove all childNodes from body.
      while ($bodyElement->childNodes[0]) {
        $bodyElement->removeChild($bodyElement->childNodes[0]);
      }
      // Add childNodes from new body.
      while ($newBodyElement->childNodes[0]) {
        $bodyElement->appendChild($newBodyElement->childNodes[0]);
      }

      // Save Global dom document to message body.
      $body = $html->saveHTML();
    }
    catch (Exception $e) {
      watchdog_exception('falcon_mail', $e, "Falcon mail formatter didn't replace relative urls on absolute. Email: " . $body);
    }

    return $body;
  }

}
