Email Configuration
===================

Falcon mailing functionality is placed in ``falcon_mail`` module. The module provides a plugin for Drupal mail system with a formatter that can wrap emails into HTML templates and replace dynamic tokens.

You can change formatter on ``admin/config/system/mailsystem`` page.

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
    $params['render_tokens']['commerce_order'] = $order;    # Array 'render_tokens' contains variables for token replacement.
    $params['token_options'] = [                            # Array 'token_options' contains options for token replaceement.
        'langcode' => $langcode,
        'callback' => 'callback_function'
    ];
    $params['replace_tokens'] = TRUE;                       # Enable replacement tokens.
    $params['theme_template'] = "<div> #$#BODY#$# </div>"   # The template where will be replaced #$#BODY#$# on $messsage['body'].

    \Drupal::service('plugin.manager.mail')->mail('your_module', 'your_mail_key', $to, $langcode, $params, $reply, $send);

The ``theme_template`` param can use the **#$#BODY#$#** variable for wrap an another html to ``theme_template`` html.
You can also find an example of working in the  ``falcon_donation`` module.