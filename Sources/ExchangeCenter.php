<?php
function flmExChangeCenter(){
    global $scripturl, $context,$smcFunc,$user_info;
    $context['post_url'] = $scripturl . '?action=profile;area=flmchange;save';
    $request = $smcFunc['db_query']('', '
                    SELECT  flm
                    FROM {db_prefix}property
                    WHERE id_member = {int:id_member}
                    LIMIT 1',
        array(
            'id_member' => $user_info['id'],
        )
    );
    $userProperty = $smcFunc['db_fetch_assoc']($request);
    $flmAmount = $userProperty['flm'];
    if (isset($_GET['save']))
    {
        $amount = $_POST['amount'];
        if ($flmAmount < $amount) {
            fatal_error('Insufficient FLM quantity');
        }

        $smcFunc['db_insert']('',
            '{db_prefix}source_user',
            array(
                'id_member' => 'int',
                'amount' => 'int',
                'type' => 'string',
                'create_at' => 'int'
            ),
            [$user_info['id'],$amount,'flm',time()],
            array()
        );
    }
}