<?php

function FRPMain(){
    global $smcFunc;

    $sa = isset($_GET['sa']) ? $_GET['sa'] : '';
    $meritFunction = [
        ''=>'frp',
        'frptransfer'=>'frptransfer',
    ];
    call_helper($meritFunction[$sa]);
}
function frptransfer(){
    global $scripturl, $context,$smcFunc,$modSettings;
    // Make sure they can view the memberlist.
    isAllowedTo(['admin_forum','frp_manage']);

    loadTemplate('FRP');
    $context['sub_template'] = 'frptransfer';

    $request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}property_transfer_log WHERE pool = {int:id} AND property = {string:property}',
        array(
            'id' => 0,
            'property' => 'sflm',
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
				SELECT   sou.id as id,mem.member_name as a,mem2.member_name as b,amount,create_at
			FROM {db_prefix}property_transfer_log AS sou 
				LEFT JOIN {db_prefix}members AS mem ON (sou.from = mem.id_member)
				LEFT JOIN {db_prefix}members AS mem2 ON (sou.to = mem2.id_member) WHERE pool = {int:id} AND property = {string:property} ORDER BY id DESC LIMIT {int:start}, {int:max}',
        array(
            'id' => 0,
            'property' => 'sflm',
            'start' => $limit,
            'max' => $modSettings['defaultMaxMembers'],
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
        $context['users'][] = $row;
    }
}
function frp(){
    global $scripturl, $context,$smcFunc,$user_info,$modSettings;
    // Make sure they can view the memberlist.
    isAllowedTo(['admin_forum','frp_manage']);

    loadTemplate('FRP');
    $context['sub_template'] = 'frp';
    if (isset($_SESSION['adm-save']))
    {
        if ($_SESSION['adm-save'] === true)
            $context['saved_successful'] = true;
        else
            $context['saved_failed'] = $_SESSION['adm-save'];

        unset($_SESSION['adm-save']);
    }
    if (isset($_SESSION['exceeded-maximum']))
    {
        if ($_SESSION['exceeded-maximum'] === true)
            $context['exceeded_maximum'] = true;

        unset($_SESSION['exceeded-maximum']);
    }
    $context['post_url'] = $scripturl . '?action=frp;save';

    $request = $smcFunc['db_query']('', '
			SELECT SUM(amount)
			FROM {db_prefix}property_logs WHERE property = {string:property}',
        array(
            'property' => 'frp'
        )
    );
    list ($context['total_issue']) =   $smcFunc['db_fetch_row']($request);

    $request = $smcFunc['db_query']('', '
			SELECT  frp
			FROM {db_prefix}property
			WHERE id_member = {int:id}
			LIMIT 1',
        array(
            'id' => 0,
        )
    );
    $poolAmount = $smcFunc['db_fetch_assoc']($request);
    $context['pool_amount'] = $poolAmount['frp'] ?? 0;

    $request = $smcFunc['db_query']('', '
			SELECT  SUM(frp)
			FROM {db_prefix}property
			WHERE id_member != {int:id}',
        array(
            'id' => 0,
        )
    );
    list ($context['user_holder']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);
    $request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}property_logs as a where a.from != {int:id} AND property = {string:property}',
        array(
            'id' => 0,
            'property' => 'frp'
        )
    );
    list ($context['num_members']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);
    $request = $smcFunc['db_query']('', '
			SELECT   mem.member_name,SUM(sou.amount) AS amount
			FROM {db_prefix}property_logs AS sou 
				INNER JOIN {db_prefix}members AS mem ON (sou.id_member = mem.id_member) where sou.from != {int:id} AND property = {string:property}  GROUP BY mem.member_name',
        array(
            'id'=>0,
            'property' => 'frp'
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
        $context['users_total'][] = $row;
    }
    $smcFunc['db_free_result']($request);
    $_REQUEST['start'] =  $_REQUEST['start']  ?? 0;
    $context['page_index'] = constructPageIndex($scripturl . '?action=frp', $_REQUEST['start'], $context['num_members'], $modSettings['defaultMaxMembers']);
    $limit = $_REQUEST['start'];
    $context['start'] = $_REQUEST['start'];
    // member-lists
    $request = $smcFunc['db_query']('', '
			SELECT  sou.id as id,sou.amount,sou.create_at, mem.member_name
			FROM {db_prefix}property_logs AS sou
				INNER JOIN {db_prefix}members AS mem ON (sou.id_member = mem.id_member) where sou.from != {int:id} AND property = {string:property} ORDER BY id DESC LIMIT {int:start}, {int:max}',
        array(
            'id' => 0,
            'start' => $limit,
            'max' => $modSettings['defaultMaxMembers'],
            'property' => 'frp'
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
        $context['users'][] = $row;
    }
    if (isset($_GET['save'])){
        checkSession();
        $amount = $_POST['amount'];
        greaterThan($amount,0);
        $request = $smcFunc['db_query']('', '
			SELECT  id_member,frp
			FROM {db_prefix}property
			WHERE id_member = {int:id}
			LIMIT 1',
            array(
                'id' => 0,
            )
        );
        $user_settings = $smcFunc['db_fetch_assoc']($request);
        if (empty($user_settings)){
            $smcFunc['db_insert']('',
                '{db_prefix}property',
                array(
                    'id_member' => 'int',
                    'frp' => 'int'
                ),
                [0,$amount],
                array()
            );
        } else {
            $smcFunc['db_query']('', '
					UPDATE {db_prefix}property
					SET frp = {int:frp}
					WHERE id_member = {int:id}',
                array(
                    'frp' => $user_settings['frp'] + $amount,
                    'id' => 0
                )
            );
        }
        $smcFunc['db_insert']('',
            '{db_prefix}property_logs',
            array(
                'id_member' => 'int',
                'amount' => 'int',
                'from' => 'int',
                'create_at' => 'int',
                'property' => 'string',
            ),
            [$user_info['id'],$amount,1,time(),'frp'],
            array()
        );
        $_SESSION['adm-save'] = true;
        redirectexit('action=frp');
    }

}

