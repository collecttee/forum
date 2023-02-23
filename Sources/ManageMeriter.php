<?php
function ManageMeriter()
{
    global $txt, $context, $scripturl;
    // Only admins, only EVER admins!
    isAllowedTo('admin_forum');

    // Let's get our things running...
    loadTemplate('ManageMeriter');
//    loadLanguage('Reports');
    $context['page_title'] = $txt['managemeriter_title'];
}