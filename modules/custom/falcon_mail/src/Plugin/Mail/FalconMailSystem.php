<?php

namespace Drupal\falcon_mail\Plugin\Mail;

use Drupal;
use Drupal\Core\Mail\Plugin\Mail\PhpMail;

/**
 * Modify the drupal mail system to use html when sending emails.
 *
 * @Mail(
 *   id = "falcon_mail_system",
 *   label = @Translation("Falcon Mailer"),
 *   description = @Translation("Provides .")
 * )
 */
class FalconMailSystem extends PhpMail {

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
    if (isset($message['params']['headers'])) {
      $message['headers'] = array_merge($message['headers'], $message['params']['headers']);
    }

    if (is_array($message['body'])) {
      $message['body'] = implode("\n\n", $message['body']);
    }

    if (isset($message['params']['replace_tokens']) && $message['params']['replace_tokens']) {

      $tokens = isset($message['params']['render_tokens']) ? $message['params']['render_tokens'] : [];
      $token_options = isset($message['params']['token_options']) ? $message['params']['token_options'] : [];

      // Replace tokens and set new body.
      $message['body'] = Drupal::token()->replace($message['body'], $tokens, $token_options);
    }

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

}
