<?php
function SendMain(){
    global $user_info,$context,$smcFunc;
    loadTemplate('Send');
    $title = $_GET['title'] ?? '';
    $context['title'] = $title;
    $context['username'] = $user_info['username'];
    $request = $smcFunc['db_query']('', '
                    SELECT  smerit,merit
                    FROM {db_prefix}property
                    WHERE id_member = {int:id}
                    LIMIT 1',
        array(
            'id' => $user_info['id'],
        )
    );
    $pool = $smcFunc['db_fetch_assoc']($request);

    $smcFunc['db_free_result']($request);
    $context['smerit_amount']  = $pool['smerit'];
    $context['merit_amount']  = $pool['merit'];
}