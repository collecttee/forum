<?php
function SetSourceUser() {
    global $scripturl, $txt, $modSettings, $context;

    // Make sure they can view the memberlist.
    isAllowedTo('view_mlist');

    loadTemplate('Merits');

}