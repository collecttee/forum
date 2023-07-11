<?php
function flm(){
    $sa = isset($_GET['sa']) ? $_GET['sa'] : '';
    $meritFunction = [
        ''=>'flmtransfer',
        'reward'=>'reward',
        'internal'=>'internal',
    ];
    call_helper($meritFunction[$sa]);
}
function flmtransfer(){
    global $scripturl, $context,$smcFunc,$user_info,$modSettings;
    $context['post_url'] = $scripturl . '?action=profile;area=flm;save';
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
    if (isset($_SESSION['not-found']))
    {
        if ($_SESSION['not-found'] === true)
            $context['not_found_user'] = true;
        else
            $context['saved_failed'] = $_SESSION['adm-save'];

        unset($_SESSION['not-found']);
    }
    if (isset($_SESSION['adm-save']))
    {
        if ($_SESSION['adm-save'] === true)
            $context['saved_successful'] = true;
        else
            $context['saved_failed'] = $_SESSION['adm-save'];

        unset($_SESSION['adm-save']);
    }
    if (isset($_GET['save'])){
        checkSession();
        $amount = $_POST['amount'];
        greaterThan($amount,0);
        if ($flmAmount < $amount) {
            fatal_error('Insufficient FLM quantity');
        }
        $username = $_POST['username'];
        if ($user_info['username'] == $username) {
            fatal_error('Cannot transfer to oneself');
        }
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
            redirectexit('action=profile;area=flm');
        }
        $request = $smcFunc['db_query']('', '
			SELECT  flm
			FROM {db_prefix}property
			WHERE id_member = {int:id}
			LIMIT 1',
            array(
                'id' => $user_settings['id_member'],
            )
        );
        $toProperty = $smcFunc['db_fetch_assoc']($request);
        if (empty($toProperty)){
            $smcFunc['db_insert']('',
                '{db_prefix}property',
                array(
                    'id_member' => 'int',
                    'flm' => 'int'
                ),
                [$user_settings['id_member'],$amount],
                array()
            );
        }else{
            $smcFunc['db_query']('', '
					UPDATE {db_prefix}property
					SET flm = {int:flm}
					WHERE id_member = {int:id}',
                array(
                    'flm' => $toProperty['flm'] + $amount,
                    'id' => $user_settings['id_member']
                )
            );
        }
        $smcFunc['db_query']('', '
					UPDATE {db_prefix}property
					SET flm = {int:flm}
					WHERE id_member = {int:id}',
            array(
                'flm' => $flmAmount - $amount,
                'id' => $user_info['id']
            )
        );
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
            [$user_info['id'],$user_settings['id_member'],$amount,time(),1,'flm'],
            array()
        );
        $_SESSION['adm-save'] = true;
        redirectexit('action=profile;area=flm');
    }


    $request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}property_transfer_log as a WHERE property = {string:property} AND pool = {int:id} AND a.from = {int:user}',
        array(
            'user'=>$user_info['id'],
            'id' => 1,
            'property' => 'flm',
        )
    );
    list ($context['num_members']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);
    $_REQUEST['start'] =  $_REQUEST['start']  ?? 0;
    $context['page_index'] = constructPageIndex($scripturl . '?action=profile;area=flm', $_REQUEST['start'], $context['num_members'], $modSettings['defaultMaxMembers']);
    $limit = $_REQUEST['start'];
    $context['start'] = $_REQUEST['start'];
    // member-lists
    $request = $smcFunc['db_query']('', '
				SELECT   sou.id as id,mem.member_name as a,mem2.member_name as b,mem2.pid,amount,create_at
			FROM {db_prefix}property_transfer_log AS sou 
				LEFT JOIN {db_prefix}members AS mem ON (sou.from = mem.id_member)
				LEFT JOIN {db_prefix}members AS mem2 ON (sou.to = mem2.id_member)  WHERE property = {string:property} AND pool = {int:id} AND sou.from = {int:user} ORDER BY id DESC LIMIT {int:start}, {int:max}',
        array(
            'user'=>$user_info['id'],
            'id' => 1,
            'property' => 'flm',
            'start' => $limit,
            'max' => $modSettings['defaultMaxMembers'],
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
        $context['users'][] = $row;
    }
}
function reward(){
    global $scripturl, $context,$smcFunc,$user_info,$modSettings;
    $context['sub_template'] = 'reward';
    $request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}property_transfer_log as a WHERE property = {string:property} AND pool = {int:id} AND a.to = {int:user}',
        array(
            'user'=>$user_info['id'],
            'id' => 1,
            'property' => 'sflm',
        )
    );
    list ($context['num_members']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);
    $_REQUEST['start'] =  $_REQUEST['start']  ?? 0;
    $context['page_index'] = constructPageIndex($scripturl . '?action=profile;area=flm', $_REQUEST['start'], $context['num_members'], $modSettings['defaultMaxMembers']);
    $limit = $_REQUEST['start'];
    $context['start'] = $_REQUEST['start'];
    // member-lists
    $request = $smcFunc['db_query']('', '
				SELECT   sou.id as id,mem.member_name as a,mem2.member_name as b,mem.pid,amount,create_at
			FROM {db_prefix}property_transfer_log AS sou 
				LEFT JOIN {db_prefix}members AS mem ON (sou.from = mem.id_member)
				LEFT JOIN {db_prefix}members AS mem2 ON (sou.to = mem2.id_member)  WHERE property = {string:property} AND pool = {int:id} AND sou.to = {int:user} ORDER BY id DESC LIMIT {int:start}, {int:max}',
        array(
            'user'=>$user_info['id'],
            'id' => 1,
            'property' => 'sflm',
            'start' => $limit,
            'max' => $modSettings['defaultMaxMembers'],
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
        $context['users'][] = $row;
    }
}
function internal(){
    global $scripturl, $context,$smcFunc,$user_info,$modSettings;
    $context['sub_template'] = 'internal';
    $request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}property_transfer_log as a WHERE property = {string:property} AND pool = {int:id} AND a.to = {int:user}',
        array(
            'user'=>$user_info['id'],
            'id' => 1,
            'property' => 'flm',
        )
    );
    list ($context['num_members']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);
    $_REQUEST['start'] =  $_REQUEST['start']  ?? 0;
    $context['page_index'] = constructPageIndex($scripturl . '?action=profile;area=flm', $_REQUEST['start'], $context['num_members'], $modSettings['defaultMaxMembers']);
    $limit = $_REQUEST['start'];
    $context['start'] = $_REQUEST['start'];
    // member-lists
    $request = $smcFunc['db_query']('', '
				SELECT   sou.id as id,mem.member_name as a,mem2.member_name as b,mem.pid,amount,create_at
			FROM {db_prefix}property_transfer_log AS sou 
				LEFT JOIN {db_prefix}members AS mem ON (sou.from = mem.id_member)
				LEFT JOIN {db_prefix}members AS mem2 ON (sou.to = mem2.id_member)  WHERE property = {string:property} AND pool = {int:id} AND sou.to = {int:user} ORDER BY id DESC LIMIT {int:start}, {int:max}',
        array(
            'user'=>$user_info['id'],
            'id' => 1,
            'property' => 'flm',
            'start' => $limit,
            'max' => $modSettings['defaultMaxMembers'],
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
        $context['users'][] = $row;
    }
}
function zealy(){
    global $scripturl, $context,$smcFunc,$user_info,$modSettings,$zealySubdomain, $zealyLeaderboardApiKey;
    $context['sub_template'] = 'zealy';
    $_REQUEST['start'] =  $_REQUEST['start']  ?? 0;
    $limit = $_REQUEST['start'];
    $context['start'] = $_REQUEST['start'];
    $page = $limit / $modSettings['defaultMaxMembers'];
    $ret  = curlGet("https://api.zealy.io/communities/{$zealySubdomain}/leaderboard?limit={$modSettings['defaultMaxMembers']}&page={$page}",[],["x-api-key:{$zealyLeaderboardApiKey}"]);
    $ret = json_decode($ret,1);
    if (isset($ret['totalPages'])) {
        $context['num_members'] = $ret['totalPages'] * $modSettings['defaultMaxMembers'];
        $context['page_index'] = constructPageIndex($scripturl . '?action=profile;area=zealy;u='.$user_info['id'], $_REQUEST['start'], $context['num_members'], $modSettings['defaultMaxMembers']);
    }
    if (!empty($ret['leaderboard'])){
        foreach ($ret['leaderboard'] as $row) {
            $PID = 0;
            if (isset($row['address'])){
                $request = $smcFunc['db_query']('', '
                    SELECT pid
                    FROM {db_prefix}members
                    WHERE address = {string:address}
                    LIMIT 1',
                    array(
                        'address' => $row['address'],
                    )
                );
                $user= $smcFunc['db_fetch_assoc']($request);
                $PID = $user['pid'];
            }
            $row['pid'] = $PID;
            $context['users'][] = $row;
        }
    }



}

