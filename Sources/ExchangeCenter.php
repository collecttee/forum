<?php
function flmExChangeCenter(){
    global $scripturl, $context,$smcFunc,$user_info,$modSettings;
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
    $context['flm']=$flmAmount;
    $request = $smcFunc['db_query']('', '
                    SELECT  min,max
                    FROM {db_prefix}exchange_limit
                    WHERE property = {string:property}
                    LIMIT 1',
        array(
            'property' => 'flm',
        )
    );
    $pool = $smcFunc['db_fetch_assoc']($request);
    $context['min'] = $pool['min'] ?? 0;
    $context['max'] = $pool['max']  ?? 0;
    if (isset($_SESSION['adm-save']))
    {
        if ($_SESSION['adm-save'] === true)
            $context['saved_successful'] = true;
        else
            $context['saved_failed'] = $_SESSION['adm-save'];

        unset($_SESSION['adm-save']);
    }
    if (isset($_GET['save']))
    {
//        checkSession();
        $amount = $_POST['amount'];
        greaterThan($amount,0);
        if ($flmAmount < $amount) {
            fatal_error('Insufficient FLM quantity');
        }

        $smcFunc['db_insert']('',
            '{db_prefix}apply_withdraw',
            array(
                'id_member' => 'int',
                'amount' => 'int',
                'type' => 'string',
                'create_at' => 'int'
            ),
            [$user_info['id'],$amount,'flm',time()],
            array()
        );
        $smcFunc['db_query']('', '
					UPDATE {db_prefix}property
					SET flm = {int:flm}
					WHERE id_member = {int:id}',
            array(
                'flm' => $flmAmount - $amount,
                'id' => $user_info['id']
            )
        );

        $_SESSION['adm-save'] = true;
        redirectexit('action=profile;area=flmchange;u='.$user_info['id']);
    }
    $request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}apply_withdraw WHERE type = {string:type} AND id_member = {int:id}',
        array(
            'id' => $user_info['id'],
            'type' => 'flm',
        )
    );
    list ($context['num_members']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);
    $_REQUEST['start'] =  $_REQUEST['start']  ?? 0;
    $context['page_index'] = constructPageIndex($scripturl . '?action=flm;sa=sflmtransfer', $_REQUEST['start'], $context['num_members'], $modSettings['defaultMaxMembers']);
    $limit = $_REQUEST['start'];
    $context['start'] = $_REQUEST['start'];
    // member-lists
    $request = $smcFunc['db_query']('', '
				SELECT   a.*,mem.address
			FROM {db_prefix}apply_withdraw as a LEFT JOIN {db_prefix}members AS mem ON (a.id_member = mem.id_member)  WHERE type = {string:type} AND a.id_member = {int:id} ORDER BY id DESC LIMIT {int:start}, {int:max}',
        array(
            'id' => $user_info['id'],
            'type' => 'flm',
            'start' => $limit,
            'max' => $modSettings['defaultMaxMembers'],
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
        $context['users'][] = $row;
    }
}
