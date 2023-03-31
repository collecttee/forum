<?php
function SetSourceUser() {
    global $scripturl, $context,$smcFunc,$user_info;
    // Make sure they can view the memberlist.
    isAllowedTo(['admin_forum','flm_manage']);

    loadTemplate('FLM');
    $context['post_url'] = $scripturl . '?action=flm;save';
    $context['delete_url'] = $scripturl . '?action=flm';
    $request = $smcFunc['db_query']('', '
                    SELECT  sflm
                    FROM {db_prefix}property
                    WHERE id_member = {int:id}
                    LIMIT 1',
        array(
            'id' => 0,
        )
    );
    $pool = $smcFunc['db_fetch_assoc']($request);
    $smcFunc['db_free_result']($request);
    $context['pool_amount'] = $pool['sflm'];
    if (isset($_POST['work']) && $_POST['work'] == 'delete') {
        checkSession();
        if (isset($_POST['transfer']) && $_POST['transfer'] == 'Transfer sFLM') {
            $amount = $_POST['amount'];
            $sum = array_sum($amount);
            $id_member = $_POST['id_member'];
            if ($sum > $pool['sflm']) {
                $_SESSION['pass-max'] = true;
                redirectexit('action=flm');
            }
            $smcFunc['db_query']('', '
                        UPDATE {db_prefix}property
                        SET sflm = {int:sflm}
                        WHERE id_member = {int:id}',
                array(
                    'sflm' =>$pool['sflm'] - $sum,
                    'id' => 0
                )
            );

            foreach ($amount as $k=> $v) {
                if(!empty($v)){
                    $request = $smcFunc['db_query']('', '
                    SELECT  id_member,sflm
                    FROM {db_prefix}property
                    WHERE id_member = {int:id_member}
                    LIMIT 1',
                        array(
                            'id_member' => $id_member[$k],
                        )
                    );
                    $exists = $smcFunc['db_fetch_assoc']($request);
                    $smcFunc['db_free_result']($request);
                    if (!empty($exists)){
                        $smcFunc['db_query']('', '
                        UPDATE {db_prefix}property
                        SET sflm = {int:sflm}
                        WHERE id_member = {int:id}',
                                array(
                                    'sflm' => $exists['sflm'] + $v,
                                    'id' => $id_member[$k]
                            )
                        );
                    }else{
                        $smcFunc['db_insert']('',
                            '{db_prefix}property',
                            array(
                                'id_member' => 'int',
                                'sflm' => 'int'
                            ),
                            [$id_member[$k],$v],
                            array()
                        );
                    }
                    $smcFunc['db_insert']('',
                        '{db_prefix}property_transfer_log',
                        array(
                            'from' => 'int',
                            'to' => 'int',
                            'amount' => 'int',
                            'create_at' => 'int',
                            'pool' => 'int',
                            'property' => 'string',
                        ),
                        [$user_info['id'],$id_member[$k],$v,time(),0,'sflm'],
                        array()
                    );
                }
            }

            $_SESSION['adm-save'] = true;


        }else{
            $delete = $_POST['delete'];
            $smcFunc['db_query']('', '
		DELETE FROM {db_prefix}source_user
		WHERE source = {int:source} AND id IN ({array_int:users})',
                array(
                    'source'=>1,
                    'users' => $delete
                )
            );
        }

        redirectexit('action=flm');
    }
    if (isset($_SESSION['adm-save']))
    {
        if ($_SESSION['adm-save'] === true)
            $context['saved_successful'] = true;
        else
            $context['saved_failed'] = $_SESSION['adm-save'];

        unset($_SESSION['adm-save']);
    }
    if (isset($_SESSION['not-found']))
    {
        if ($_SESSION['not-found'] === true)
            $context['not_found_user'] = true;
        else
            $context['saved_failed'] = $_SESSION['adm-save'];

        unset($_SESSION['not-found']);
    }
    if (isset($_SESSION['exists']))
    {
        if ($_SESSION['exists'] === true)
            $context['exists'] = true;
        else
            $context['saved_failed'] = $_SESSION['adm-save'];

        unset($_SESSION['exists']);
    }
    if (isset($_SESSION['pass-max']))
    {
        if ($_SESSION['pass-max'] === true)
            $context['pass-max'] = true;
        else
            $context['saved_failed'] = $_SESSION['adm-save'];

        unset($_SESSION['pass-max']);
    }
    // member-lists
    $request = $smcFunc['db_query']('', '
			SELECT  sou.id as id,mem.id_member, mem.member_name,mem.address,pro.sflm as sflm
			FROM {db_prefix}source_user AS sou
				INNER JOIN {db_prefix}members AS mem ON (sou.id_member = mem.id_member)
				LEFT JOIN {db_prefix}property AS pro ON (pro.id_member = sou.id_member) WHERE sou.source = {int:source}',
        array(
            'source' => 1
        )
    );

    while ($row = $smcFunc['db_fetch_assoc']($request)) {
        $context['users'][] = $row;
    }


    if (isset($_GET['save']))
    {
        checkSession();
        $username = $_POST['username'];
        $request = $smcFunc['db_query']('', '
			SELECT  address, id_member,  member_name
			FROM {db_prefix}members
			WHERE member_name = {string:username}
			LIMIT 1',
            array(
                'username' => $username,
            )
        );
        $user_settings = $smcFunc['db_fetch_assoc']($request);
        if (empty($user_settings)){
            $_SESSION['not-found'] = true;
            redirectexit('action=flm');
        }
        $request = $smcFunc['db_query']('', '
			SELECT  id_member
			FROM {db_prefix}source_user
			WHERE source = {int:source} AND id_member = {int:id_member}
			LIMIT 1',
            array(
                'source' => 1,
                'id_member' => $user_settings['id_member'],
            )
        );
        $exists = $smcFunc['db_fetch_assoc']($request);
        if (!empty($exists)){
            $_SESSION['exists'] = true;
            redirectexit('action=flm');
        }
        $smcFunc['db_insert']('',
            '{db_prefix}source_user',
            array(
                'id_member' => 'int',
                'source' => 'int',
                'create_at' => 'int'
            ),
            [$user_settings['id_member'],1,time()],
            array()
        );
        $_SESSION['adm-save'] = true;
        redirectexit('action=flm');
    }
}
function FLMMain(){
    global $smcFunc;
    $request = $smcFunc['db_query']('', '
			SELECT  id,flm_max_limit
			FROM {db_prefix}property_max
			WHERE id = {int:id}
			LIMIT 1',
        array(
            'id' => 2
        )
    );
    $result = $smcFunc['db_fetch_assoc']($request);
    $smcFunc['db_free_result']($request);
    $ret = $result['flm_max_limit'] ?? 0;
    if ($ret == 1) {
        fatal_error('Feature has been disabled');
    }
    $sa = isset($_GET['sa']) ? $_GET['sa'] : '';
    $meritFunction = [
        ''=>'SetSourceUser',
        'sflmtransfer'=>'sFLMTransfer',
        'sflm'=>'sflm',
        'flmexchange'=>'flmexchange',
        'not'=>'notReview',
        'emerit'=>'emerit',
        'usersflmTransfer'=>'usersflmTransfer',
    ];
    call_helper($meritFunction[$sa]);
}
function usersflmTransfer(){
    global $scripturl, $context,$smcFunc,$modSettings;
    // Make sure they can view the memberlist.
    isAllowedTo(['admin_forum','flm_manage']);

    loadTemplate('FLM');
    $context['sub_template'] = 'usersFLMTransfer';

    $request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}property_transfer_log WHERE pool = {int:id} AND property = {string:property}',
        array(
            'id' => 1,
            'property' => 'sflm'
        )
    );
    list ($context['num_members']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);
    $_REQUEST['start'] =  $_REQUEST['start']  ?? 0;
    $context['page_index'] = constructPageIndex($scripturl . '?action=flm;sa=usersflmTransfer', $_REQUEST['start'], $context['num_members'], $modSettings['defaultMaxMembers']);
    $limit = $_REQUEST['start'];
    $context['start'] = $_REQUEST['start'];
    // member-lists
    $request = $smcFunc['db_query']('', '
				SELECT   sou.id as id,mem.member_name as a,mem2.member_name as b,amount,create_at
			FROM {db_prefix}property_transfer_log AS sou 
				LEFT JOIN {db_prefix}members AS mem ON (sou.from = mem.id_member)
				LEFT JOIN {db_prefix}members AS mem2 ON (sou.to = mem2.id_member) WHERE pool = {int:id} AND property = {string:property} ORDER BY id DESC LIMIT {int:start}, {int:max}',
        array(
            'id' => 1,
            'start' => $limit,
            'property' => 'sflm',
            'max' => $modSettings['defaultMaxMembers'],
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
        $context['users'][] = $row;
    }
}
function sFLMTransfer(){
    global $scripturl, $context,$smcFunc,$modSettings;
    // Make sure they can view the memberlist.
    isAllowedTo(['admin_forum','flm_manage']);

    loadTemplate('FLM');
    $context['sub_template'] = 'sFLMTransfer';

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
function sflm(){
    global $scripturl, $context,$smcFunc,$user_info,$modSettings;
    // Make sure they can view the memberlist.
    isAllowedTo(['admin_forum','flm_manage']);

    loadTemplate('FLM');
    $context['sub_template'] = 'sFLM';
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
    $context['post_url'] = $scripturl . '?action=flm;save;sa=sflm';

    $request = $smcFunc['db_query']('', '
			SELECT SUM(amount)
			FROM {db_prefix}property_logs WHERE property = {string:property}',
        array(
            'property' => 'sflm'
        )
    );
    list ($context['total_issue']) =   $smcFunc['db_fetch_row']($request);

    $request = $smcFunc['db_query']('', '
			SELECT  sflm
			FROM {db_prefix}property
			WHERE id_member = {int:id}
			LIMIT 1',
        array(
            'id' => 0,
        )
    );
    $poolAmount = $smcFunc['db_fetch_assoc']($request);
    $context['pool_amount'] = $poolAmount['sflm'] ?? 0;

    $request = $smcFunc['db_query']('', '
			SELECT  SUM(sflm)
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
            'property' => 'sflm'
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
            'property' => 'sflm'
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
        $context['users_total'][] = $row;
    }
    $smcFunc['db_free_result']($request);
    $_REQUEST['start'] =  $_REQUEST['start']  ?? 0;
    $context['page_index'] = constructPageIndex($scripturl . '?action=flm;sa=sflm', $_REQUEST['start'], $context['num_members'], $modSettings['defaultMaxMembers']);
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
            'property' => 'sflm'
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
        $context['users'][] = $row;
    }
    if (isset($_GET['save'])){
        checkSession();
        $amount = $_POST['amount'];
        $request = $smcFunc['db_query']('', '
			SELECT  id,flm_max_limit
			FROM {db_prefix}property_max
			WHERE id = {int:id}
			LIMIT 1',
            array(
                'id' => 1
            )
        );
        $result = $smcFunc['db_fetch_assoc']($request);
        $smcFunc['db_free_result']($request);
        $ret = $result['flm_max_limit'] ?? 0;
        if ($amount > $ret) {
            $_SESSION['exceeded-maximum'] = true;
            redirectexit('action=flm;sa=sflm');
        }
        $request = $smcFunc['db_query']('', '
			SELECT  id_member,sflm
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
                    'sflm' => 'int'
                ),
                [0,$amount],
                array()
            );
        } else {
            $smcFunc['db_query']('', '
					UPDATE {db_prefix}property
					SET sflm = {int:sflm}
					WHERE id_member = {int:id}',
                array(
                    'sflm' => $user_settings['sflm'] + $amount,
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
            [$user_info['id'],$amount,1,time(),'sflm'],
            array()
        );
        $_SESSION['adm-save'] = true;
        redirectexit('action=flm;sa=sflm');
    }

}

function flmexchange(){
    global $scripturl, $context,$smcFunc,$user_info,$modSettings;
    // Make sure they can view the memberlist.
    isAllowedTo(['admin_forum','flm_manage']);

    loadTemplate('FLM');
    $context['sub_template'] = 'flmexchange';
    $context['post_url'] = $scripturl . '?action=flm;sa=flmexchange;save';
    $context['modify_url'] = $scripturl . '?action=flm;sa=flmexchange;modify';
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
    if (isset($_GET['modify']))
    {
        checkSession();
        if (isset($_POST['do_state'])){
            $pass = $_POST['pass'];
            $reject = $_POST['reject'];
        }

    }
    if (isset($_GET['save']))
    {
        checkSession();
        $min = $_POST['min'];
        $max = $_POST['max'];
        if (empty($pool)){
            $smcFunc['db_insert']('',
                '{db_prefix}exchange_limit',
                array(
                    'min' => 'int',
                    'max' => 'int',
                    'property' => 'string',
                ),
                [$min,$max,'flm'],
                array()
            );
        }else{
            $smcFunc['db_query']('', '
                UPDATE {db_prefix}exchange_limit
                SET min = {int:min},
                max = {int:max}
                WHERE property = {string:property}',
                array(
                    'min' => $min,
                    'max' => $max,
                    'property' => 'flm'
                )
            );
        }
        $_SESSION['adm-save'] = true;
        redirectexit('action=flm;sa=flmexchange');

    }

    $request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}apply_withdraw WHERE type = {string:type}',
        array(
            'type' => 'flm',
        )
    );
    list ($context['num_members']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);
    $_REQUEST['start'] =  $_REQUEST['start']  ?? 0;
    $context['page_index'] = constructPageIndex($scripturl . '?action=flm;sa=flmexchange', $_REQUEST['start'], $context['num_members'], $modSettings['defaultMaxMembers']);
    $limit = $_REQUEST['start'];
    $context['start'] = $_REQUEST['start'];
    // member-lists
    $request = $smcFunc['db_query']('', '
				SELECT   a.*,mem.member_name,mem.pid,mem.address
			FROM {db_prefix}apply_withdraw as a LEFT JOIN {db_prefix}members AS mem ON (a.id_member = mem.id_member)  WHERE type = {string:type} ORDER BY id DESC LIMIT {int:start}, {int:max}',
        array(
            'type' => 'flm',
            'start' => $limit,
            'max' => $modSettings['defaultMaxMembers'],
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
        $context['users'][] = $row;
    }

}
function notReview(){
    global $scripturl, $context,$smcFunc,$user_info,$modSettings;
    // Make sure they can view the memberlist.
    isAllowedTo(['admin_forum','flm_manage']);

    loadTemplate('FLM');
    $context['sub_template'] = 'notReview';
    $context['modify_url'] = $scripturl . '?action=flm;sa=not;modify';
    if (isset($_SESSION['adm-save']))
    {
        if ($_SESSION['adm-save'] === true)
            $context['saved_successful'] = true;
        else
            $context['saved_failed'] = $_SESSION['adm-save'];

        unset($_SESSION['adm-save']);
    }
    if (isset($_GET['modify']))
    {
        checkSession();
        if (isset($_POST['do_state'])){
            $pass = $_POST['pass'];
            $reject = $_POST['reject'];
            if (!empty($pass)){
                $smcFunc['db_query']('', '
					UPDATE {db_prefix}apply_withdraw
					SET state = {int:state}
					WHERE id IN ({array_int:users})',
                    array(
                        'state' => 1,
                        'id' => $pass
                    )
                );
            }
            if (!empty($reject)){

                $smcFunc['db_query']('', '
					UPDATE {db_prefix}apply_withdraw
					SET state = {int:state}
					WHERE id IN ({array_int:id})',
                    array(
                        'state' => 2,
                        'id' => $reject
                    )
                );
            }
        }
        $_SESSION['adm-save'] = true;
        redirectexit('action=flm;sa=not');

    }

    $request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}apply_withdraw WHERE type = {string:type} AND state = {int:state}',
        array(
            'type' => 'flm',
            'state'=>0,
        )
    );
    list ($context['num_members']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);
    $_REQUEST['start'] =  $_REQUEST['start']  ?? 0;
    $context['page_index'] = constructPageIndex($scripturl . '?action=flm;sa=flmexchange', $_REQUEST['start'], $context['num_members'], $modSettings['defaultMaxMembers']);
    $limit = $_REQUEST['start'];
    $context['start'] = $_REQUEST['start'];
    // member-lists
    $request = $smcFunc['db_query']('', '
				SELECT   a.*,mem.member_name,mem.pid,mem.address
			FROM {db_prefix}apply_withdraw as a LEFT JOIN {db_prefix}members AS mem ON (a.id_member = mem.id_member)  WHERE type = {string:type} AND state = {int:state} ORDER BY id DESC LIMIT {int:start}, {int:max}',
        array(
            'type' => 'flm',
            'state'=>0,
            'start' => $limit,
            'max' => $modSettings['defaultMaxMembers'],
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
        $context['users'][] = $row;
    }

}
function emerit(){
    global $scripturl, $context,$smcFunc,$user_info,$modSettings;
    // Make sure they can view the memberlist.
    isAllowedTo(['admin_forum','flm_manage']);

    loadTemplate('Merits');
    $context['sub_template'] = 'emerit';

    $request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}smerit_logs as a where a.from != {int:id}',
        array(
            'id' => 1
        )
    );
    list ($context['num_members']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);
    $_REQUEST['start'] =  $_REQUEST['start']  ?? 0;
    $context['page_index'] = constructPageIndex($scripturl . '?action=flm;sa=emerit', $_REQUEST['start'], $context['num_members'], $modSettings['defaultMaxMembers']);
    $limit = $_REQUEST['start'];
    // member-lists
    $request = $smcFunc['db_query']('', '
			SELECT  sou.id as id,sou.amount,sou.create_at, mem.member_name
			FROM {db_prefix}emerit_logs AS sou
				INNER JOIN {db_prefix}members AS mem ON (sou.id_member = mem.id_member) ORDER BY id DESC LIMIT {int:start}, {int:max}',
        array(
            'start' => $limit,
            'max' => $modSettings['defaultMaxMembers'],
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
        $context['users'][] = $row;
    }

}
