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
    $context['post_url'] = $scripturl . '?action=admin;area=managemeriter;save';
    // Saving the settings?
    if (isset($_GET['save']))
    {
        checkSession();
        $_SESSION['adm-save'] = true;
        redirectexit('action=admin;area=managemeriter');
    }
}