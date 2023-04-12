<?php
function SetSourceUser() {
    global $scripturl, $context,$smcFunc,$user_info;
    // Make sure they can view the memberlist.
    isAllowedTo(['admin_forum','merit_manage']);

    loadTemplate('Merits');
    $context['post_url'] = $scripturl . '?action=merit;save';
    $context['delete_url'] = $scripturl . '?action=merit';
    $request = $smcFunc['db_query']('', '
                    SELECT  smerit
                    FROM {db_prefix}property
                    WHERE id_member = {int:id}
                    LIMIT 1',
        array(
            'id' => 0,
        )
    );
    $pool = $smcFunc['db_fetch_assoc']($request);
    $smcFunc['db_free_result']($request);
    $context['pool_amount'] = $pool['smerit'];
    if (isset($_POST['work']) && $_POST['work'] == 'delete') {
        checkSession();
        if (isset($_POST['transfer']) && $_POST['transfer'] == 'Transfer sMerit') {
            $amount = $_POST['amount'];
            foreach ($amount as $v) {
                if (!empty($v)){
                    greaterThan($v,0);
                }
            }
            $sum = array_sum($amount);
            $id_member = $_POST['id_member'];
            if ($sum > $pool['smerit']) {
                $_SESSION['pass-max'] = true;
                redirectexit('action=merit');
            }

            $smcFunc['db_query']('', '
                        UPDATE {db_prefix}property
                        SET smerit = {int:smerit}
                        WHERE id_member = {int:id}',
                array(
                    'smerit' =>$pool['smerit'] - $sum,
                    'id' => 0
                )
            );
            foreach ($amount as $k=> $v) {
                if(!empty($v)){
                    $request = $smcFunc['db_query']('', '
                    SELECT  id_member,smerit
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
                        SET smerit = {int:smerit}
                        WHERE id_member = {int:id}',
                                array(
                                    'smerit' => $exists['smerit'] + $v,
                                    'id' => $id_member[$k]
                            )
                        );
                    }else{
                        $smcFunc['db_insert']('',
                            '{db_prefix}property',
                            array(
                                'id_member' => 'int',
                                'smerit' => 'int'
                            ),
                            [$id_member[$k],$v],
                            array()
                        );
                    }
                    $smcFunc['db_insert']('',
                        '{db_prefix}smerit_transfer_log',
                        array(
                            'from' => 'int',
                            'to' => 'int',
                            'amount' => 'int',
                            'create_at' => 'int',
                            'pool' => 'int'
                        ),
                        [$user_info['id'],$id_member[$k],$v,time(),0],
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
                    'users' => $delete,
                    'source' => 0
                )
            );
        }

        redirectexit('action=merit');
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
			SELECT  sou.id as id,mem.id_member, mem.member_name,mem.address,pro.smerit as smerit
			FROM {db_prefix}source_user AS sou
				INNER JOIN {db_prefix}members AS mem ON (sou.id_member = mem.id_member)
				LEFT JOIN {db_prefix}property AS pro ON (pro.id_member = sou.id_member) WHERE sou.source = {int:source}',
        array(
            'source' => 0
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
            redirectexit('action=merit');
        }
        $request = $smcFunc['db_query']('', '
			SELECT  id_member
			FROM {db_prefix}source_user
			WHERE source = {int:source} AND id_member = {int:id_member}
			LIMIT 1',
            array(
                'source' => 0,
                'id_member' => $user_settings['id_member'],
            )
        );
        $exists = $smcFunc['db_fetch_assoc']($request);
        if (!empty($exists)){
            $_SESSION['exists'] = true;
            redirectexit('action=merit');
        }
        $smcFunc['db_insert']('',
            '{db_prefix}source_user',
            array(
                'id_member' => 'int',
                'source' => 'int',
                'create_at' => 'int'
            ),
            [$user_settings['id_member'],0,time()],
            array()
        );
        $_SESSION['adm-save'] = true;
        redirectexit('action=merit');
    }
}
function MeritMain(){
    global $smcFunc;
    $request = $smcFunc['db_query']('', '
			SELECT  id,merit_max_limit
			FROM {db_prefix}smerit_max
			WHERE id = {int:id}
			LIMIT 1',
        array(
            'id' => 2
        )
    );
    $result = $smcFunc['db_fetch_assoc']($request);
    $smcFunc['db_free_result']($request);
    $ret = $result['merit_max_limit'] ?? 0;
    if ($ret == 1) {
        fatal_error('Feature has been disabled');
    }
    $sa = isset($_GET['sa']) ? $_GET['sa'] : '';
    $meritFunction = [
        ''=>'SetSourceUser',
        'smerittransfer'=>'sMeritTransfer',
        'smerit'=>'smerit',
        'systemsMerit'=>'systemsMerit',
        'sMeritTransfer'=>'sMeritTransfer',
        'emerit'=>'emerit',
        'usersMeritTransfer'=>'usersMeritTransfer',
    ];
    call_helper($meritFunction[$sa]);
}
function usersMeritTransfer(){
    global $scripturl, $context,$smcFunc,$modSettings;
    // Make sure they can view the memberlist.
    isAllowedTo(['admin_forum','merit_manage']);

    loadTemplate('Merits');
    $context['sub_template'] = 'usersMeritTransfer';

    $request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}smerit_transfer_log WHERE pool = {int:id}',
        array(
            'id' => 1
        )
    );
    list ($context['num_members']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);
    $_REQUEST['start'] =  $_REQUEST['start']  ?? 0;
    $context['page_index'] = constructPageIndex($scripturl . '?action=merit;sa=usersMeritTransfer', $_REQUEST['start'], $context['num_members'], $modSettings['defaultMaxMembers']);
    $limit = $_REQUEST['start'];
    $context['start'] = $_REQUEST['start'];
    // member-lists
    $request = $smcFunc['db_query']('', '
				SELECT   sou.id as id,mem.member_name as a,mem2.member_name as b,amount,create_at
			FROM {db_prefix}smerit_transfer_log AS sou 
				LEFT JOIN {db_prefix}members AS mem ON (sou.from = mem.id_member)
				LEFT JOIN {db_prefix}members AS mem2 ON (sou.to = mem2.id_member) WHERE pool = {int:id} ORDER BY id DESC LIMIT {int:start}, {int:max}',
        array(
            'id' => 1,
            'start' => $limit,
            'max' => $modSettings['defaultMaxMembers'],
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
        $context['users'][] = $row;
    }
}
function sMeritTransfer(){
    global $scripturl, $context,$smcFunc,$modSettings;
    // Make sure they can view the memberlist.
    isAllowedTo(['admin_forum','merit_manage']);

    loadTemplate('Merits');
    $context['sub_template'] = 'sMeritTransfer';

    $request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}smerit_transfer_log WHERE pool = {int:id}',
        array(
            'id' => 0
        )
    );
    list ($context['num_members']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);
    $_REQUEST['start'] =  $_REQUEST['start']  ?? 0;
    $context['page_index'] = constructPageIndex($scripturl . '?action=merit;sa=sMeritTransfer', $_REQUEST['start'], $context['num_members'], $modSettings['defaultMaxMembers']);
    $limit = $_REQUEST['start'];
    $context['start'] = $_REQUEST['start'];
    // member-lists
    $request = $smcFunc['db_query']('', '
				SELECT   sou.id as id,mem.member_name as a,mem2.member_name as b,amount,create_at
			FROM {db_prefix}smerit_transfer_log AS sou 
				LEFT JOIN {db_prefix}members AS mem ON (sou.from = mem.id_member)
				LEFT JOIN {db_prefix}members AS mem2 ON (sou.to = mem2.id_member) WHERE pool = {int:id} ORDER BY id DESC LIMIT {int:start}, {int:max}',
        array(
            'id' => 0,
            'start' => $limit,
            'max' => $modSettings['defaultMaxMembers'],
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
        $context['users'][] = $row;
    }
}
function smerit(){
    global $scripturl, $context,$smcFunc,$user_info,$modSettings;
    // Make sure they can view the memberlist.
    isAllowedTo(['admin_forum','merit_manage']);

    loadTemplate('Merits');
    $context['sub_template'] = 'smerit';
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
    $context['post_url'] = $scripturl . '?action=merit;save;sa=smerit';

    $request = $smcFunc['db_query']('', '
			SELECT SUM(amount)
			FROM {db_prefix}smerit_logs',
        array(

        )
    );
    list ($context['total_issue']) =   $smcFunc['db_fetch_row']($request);

    $request = $smcFunc['db_query']('', '
			SELECT  smerit
			FROM {db_prefix}property
			WHERE id_member = {int:id}
			LIMIT 1',
        array(
            'id' => 0,
        )
    );
    $poolAmount = $smcFunc['db_fetch_assoc']($request);
    $context['pool_amount'] = $poolAmount['smerit'] ?? 0;

    $request = $smcFunc['db_query']('', '
			SELECT  SUM(smerit)
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
			FROM {db_prefix}smerit_logs as a where a.from != {int:id}',
        array(
            'id' => 0
        )
    );
    list ($context['num_members']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);
    $request = $smcFunc['db_query']('', '
			SELECT   mem.member_name,SUM(sou.amount) AS amount
			FROM {db_prefix}smerit_logs AS sou 
				INNER JOIN {db_prefix}members AS mem ON (sou.id_member = mem.id_member) where sou.from != {int:id}  GROUP BY mem.member_name',
        array(
            'id'=>0
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
        $context['users_total'][] = $row;
    }
    $smcFunc['db_free_result']($request);
    $_REQUEST['start'] =  $_REQUEST['start']  ?? 0;
    $context['page_index'] = constructPageIndex($scripturl . '?action=merit;sa=smerit', $_REQUEST['start'], $context['num_members'], $modSettings['defaultMaxMembers']);
    $limit = $_REQUEST['start'];
    $context['start'] = $_REQUEST['start'];
    // member-lists
    $request = $smcFunc['db_query']('', '
			SELECT  sou.id as id,sou.amount,sou.create_at, mem.member_name
			FROM {db_prefix}smerit_logs AS sou
				INNER JOIN {db_prefix}members AS mem ON (sou.id_member = mem.id_member) where sou.from != {int:id} ORDER BY id DESC LIMIT {int:start}, {int:max}',
        array(
            'id' => 0,
            'start' => $limit,
            'max' => $modSettings['defaultMaxMembers'],
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
			SELECT  id,merit_max_limit
			FROM {db_prefix}smerit_max
			WHERE id = {int:id}
			LIMIT 1',
            array(
                'id' => 1
            )
        );
        $result = $smcFunc['db_fetch_assoc']($request);
        $smcFunc['db_free_result']($request);
        $ret = $result['merit_max_limit'] ?? 0;
        if ($amount > $ret) {
            $_SESSION['exceeded-maximum'] = true;
            redirectexit('action=merit;sa=smerit');
        }
        $request = $smcFunc['db_query']('', '
			SELECT  id_member,smerit
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
                    'smerit' => 'int'
                ),
                [0,$amount],
                array()
            );
        } else {
            $smcFunc['db_query']('', '
					UPDATE {db_prefix}property
					SET smerit = {int:smerit}
					WHERE id_member = {int:id}',
                array(
                    'smerit' => $user_settings['smerit'] + $amount,
                    'id' => 0
                )
            );
        }
        $smcFunc['db_insert']('',
            '{db_prefix}smerit_logs',
            array(
                'id_member' => 'int',
                'amount' => 'int',
                'from' => 'int',
                'create_at' => 'int',
            ),
            [$user_info['id'],$amount,1,time()],
            array()
        );
        $_SESSION['adm-save'] = true;
        redirectexit('action=merit;sa=smerit');
    }

}

function systemsMerit(){
    global $scripturl, $context,$smcFunc,$user_info,$modSettings;
    // Make sure they can view the memberlist.
    isAllowedTo(['admin_forum','merit_manage']);

    loadTemplate('Merits');
    $context['sub_template'] = 'systemsMerit';

    $request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}smerit_logs as a where a.from != {int:id}',
        array(
            'id' => 1
        )
    );
    list ($context['num_members']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);
    $request = $smcFunc['db_query']('', '
			SELECT   mem.member_name,SUM(sou.amount) AS amount
			FROM {db_prefix}smerit_logs AS sou 
				INNER JOIN {db_prefix}members AS mem ON (sou.id_member = mem.id_member) where sou.from != {int:id}  GROUP BY mem.member_name',
        array(
            'id'=>1
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
        $context['users_total'][] = $row;
    }
    $smcFunc['db_free_result']($request);
    $_REQUEST['start'] =  $_REQUEST['start']  ?? 0;
    $context['page_index'] = constructPageIndex($scripturl . '?action=merit;sa=systemsMerit', $_REQUEST['start'], $context['num_members'], $modSettings['defaultMaxMembers']);
    $limit = $_REQUEST['start'];
    // member-lists
    $request = $smcFunc['db_query']('', '
			SELECT  sou.id as id,sou.amount,sou.create_at, mem.member_name
			FROM {db_prefix}smerit_logs AS sou
				INNER JOIN {db_prefix}members AS mem ON (sou.id_member = mem.id_member) where sou.from != {int:id} ORDER BY id DESC LIMIT {int:start}, {int:max}',
        array(
            'id' => 1,
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
    isAllowedTo(['admin_forum','merit_manage']);

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
    $context['page_index'] = constructPageIndex($scripturl . '?action=merit;sa=emerit', $_REQUEST['start'], $context['num_members'], $modSettings['defaultMaxMembers']);
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
