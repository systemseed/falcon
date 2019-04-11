Mail Configurations
===================

Falcon provides with `falcon_mail` module. The module provides plugin for mail system with formatter that can wrap emails into HTML templates and replace tokens.

You can change formatter on `admin/config/system/mailsystem`

You can configure you email with several params. Example ::

    $to = $order->getEmail();
    $langcode = 'en';
    $reply = NULL;
    $send = TRUE;

    $params = [];
    $params['from'] = 'your_email@example.com'
    $params['subject'] = "Message subject";
    $params['body'] = "<div> Html message body </div>";
    $params['headers'] = ['Content-Type' => 'text/html'];   # Enable html. You can pass any headers.
    $params['render_tokens']['commerce_order'] = $order;    # Array `render_tokens` contain variables for token replace.
    $params['token_options'] = [                            # Array `token_options` contain options for token replace.
        'langcode' => $langcode,
        'callback' => 'collback_function'
    ];
    $params['replace_tokens'] = TRUE;                       # Enable token replace.
    $params['theme_template'] = "<div> #$#BODY#$# </div>"   # The template where will be replaced #$#BODY#$# on $messsage['body'].

    \Drupal::service('plugin.manager.mail')->mail('your_module', 'your_mail_key', $to, $langcode, $params, $reply, $send);

