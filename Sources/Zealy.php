<?php

function zealyMain(){
    global $smcFunc;
    $request = $smcFunc['db_query']('', '
			SELECT  id,pause,min,max,radio
			FROM {db_prefix}zealy_config
			WHERE id = {int:id}
			LIMIT 1',
        array(
            'id' => 1
        )
    );
    $result = $smcFunc['db_fetch_assoc']($request);
    $smcFunc['db_free_result']($request);
    $ret = $result['pause'] ?? 0;
    if ($ret == 1) {
        fatal_error('Feature has been disabled');
    }
    $sa = isset($_GET['sa']) ? $_GET['sa'] : '';
    $meritFunction = [
        ''=>'xpexchange',
        'sflmtransfer'=>'sFLMTransfer',
        'sflm'=>'sflm',
        'flmexchange'=>'flmexchange',
        'not'=>'notReview',
        'reviewed'=>'reviewed',
        'usersflmTransfer'=>'usersflmTransfer',
        'complete'=>'complete',
    ];
    call_helper($meritFunction[$sa]);
}
function usersflmTransfer(){
    global $scripturl, $context,$smcFunc,$modSettings;
    // Make sure they can view the memberlist.
    isAllowedTo(['admin_forum','xp_manage']);

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
    isAllowedTo(['admin_forum','xp_manage']);

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
    isAllowedTo(['admin_forum','xp_manage']);

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
        greaterThan($amount,0);
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

function xpexchange(){
    global $scripturl, $context,$smcFunc,$user_info,$modSettings;
    // Make sure they can view the memberlist.
    isAllowedTo(['admin_forum','xp_manage']);

    loadTemplate('ZealyXP');
    $context['sub_template'] = 'xpexchange';
    $context['post_url'] = $scripturl . '?action=zealy;save';
    $context['modify_url'] = $scripturl . '?action=zealy;modify';
    if (isset($_SESSION['adm-save']))
    {
        if ($_SESSION['adm-save'] === true)
            $context['saved_successful'] = true;
        else
            $context['saved_failed'] = $_SESSION['adm-save'];

        unset($_SESSION['adm-save']);
    }

    $request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}apply_withdraw WHERE type = {string:type}',
        array(
            'type' => 'xp',
        )
    );
    list ($context['num_members']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);
    $_REQUEST['start'] =  $_REQUEST['start']  ?? 0;
    $context['page_index'] = constructPageIndex($scripturl . '?action=zealy', $_REQUEST['start'], $context['num_members'], $modSettings['defaultMaxMembers']);
    $limit = $_REQUEST['start'];
    $context['start'] = $_REQUEST['start'];
    // member-lists
    $request = $smcFunc['db_query']('', '
				SELECT   a.*,mem.member_name,mem.pid,mem.address
			FROM {db_prefix}apply_withdraw as a LEFT JOIN {db_prefix}members AS mem ON (a.id_member = mem.id_member)  WHERE type = {string:type} ORDER BY id DESC LIMIT {int:start}, {int:max}',
        array(
            'type' => 'xp',
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
    isAllowedTo(['admin_forum','xp_manage']);

    loadTemplate('ZealyXP');
    $context['sub_template'] = 'notReview';
    $context['modify_url'] = $scripturl . '?action=zealy;sa=not;modify';
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
					WHERE id IN ({array_int:id})',
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
        redirectexit('action=zealy;sa=not');

    }

    $request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}apply_withdraw WHERE type = {string:type} AND state = {int:state}',
        array(
            'type' => 'xp',
            'state'=>0,
        )
    );
    list ($context['num_members']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);
    $_REQUEST['start'] =  $_REQUEST['start']  ?? 0;
    $context['page_index'] = constructPageIndex($scripturl . '?action=zealy;sa=not', $_REQUEST['start'], $context['num_members'], $modSettings['defaultMaxMembers']);
    $limit = $_REQUEST['start'];
    $context['start'] = $_REQUEST['start'];
    // member-lists
    $request = $smcFunc['db_query']('', '
				SELECT   a.*,mem.member_name,mem.pid,mem.address
			FROM {db_prefix}apply_withdraw as a LEFT JOIN {db_prefix}members AS mem ON (a.id_member = mem.id_member)  WHERE type = {string:type} AND state = {int:state} ORDER BY id DESC LIMIT {int:start}, {int:max}',
        array(
            'type' => 'xp',
            'state'=>0,
            'start' => $limit,
            'max' => $modSettings['defaultMaxMembers'],
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
        $context['users'][] = $row;
    }

}
function reviewed(){
    global $boarddir,$scripturl, $context,$smcFunc,$user_info,$modSettings;
    // Make sure they can view the memberlist.
    isAllowedTo(['admin_forum','xp_manage']);

    loadTemplate('ZealyXP');
    $context['sub_template'] = 'reviewed';
    $context['modify_url'] = $scripturl . '?action=zealy;sa=reviewed;modify';
    $context['download_url'] = $scripturl . '?action=zealy;sa=reviewed;download';
    if (isset($_SESSION['adm-save']))
    {
        if ($_SESSION['adm-save'] === true)
            $context['saved_successful'] = true;
        else
            $context['saved_failed'] = $_SESSION['adm-save'];

        unset($_SESSION['adm-save']);
    }
    if (isset($_GET['download']))
    {
        $exportData = [];
        require_once($boarddir . '/Export.php');
        $request = $smcFunc['db_query']('', '
				SELECT   a.*,mem.member_name,mem.pid,mem.address
			FROM {db_prefix}apply_withdraw as a LEFT JOIN {db_prefix}members AS mem ON (a.id_member = mem.id_member)  WHERE type = {string:type} AND state = {int:state} AND complete = {int:complete} ORDER BY id DESC',
            array(
                'type' => 'xp',
                'state'=>1,
                'complete'=>0,
            )
        );
        while ($row = $smcFunc['db_fetch_assoc']($request)) {
            switch ($row['state']) {
                case '1':
                    $state = 'Pass';
                    break;
                case '2':
                    $state = 'Reject';
                    break;
                default:
                    $state = 'Unaudited';
                    break;
            }
            $row['state'] = $state;
            $row['complete'] = $row['complete'] == 0 ? 'No' : 'Yes';
            $exportData[] = $row;
        }

        downLoadXP('reviewed.xlsx',$exportData);


    }
    if (isset($_GET['modify']))
    {
        checkSession();
        if (isset($_POST['do_state'])){
            $complete = $_POST['complete'];
            if (!empty($complete)){
                $smcFunc['db_query']('', '
					UPDATE {db_prefix}apply_withdraw
					SET complete = {int:complete}
					WHERE id IN ({array_int:id})',
                    array(
                        'complete' => 1,
                        'id' => $complete
                    )
                );
            }
        }
        $_SESSION['adm-save'] = true;
        redirectexit('action=zealy;sa=reviewed');

    }

    $request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}apply_withdraw WHERE type = {string:type} AND state = {int:state} AND complete = {int:complete}',
        array(
            'type' => 'xp',
            'state'=>1,
            'complete'=>0,
        )
    );
    list ($context['num_members']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);
    $_REQUEST['start'] =  $_REQUEST['start']  ?? 0;
    $context['page_index'] = constructPageIndex($scripturl . '?action=zealy;sa=reviewed', $_REQUEST['start'], $context['num_members'], $modSettings['defaultMaxMembers']);
    $limit = $_REQUEST['start'];
    $context['start'] = $_REQUEST['start'];
    // member-lists
    $request = $smcFunc['db_query']('', '
				SELECT   a.*,mem.member_name,mem.pid,mem.address
			FROM {db_prefix}apply_withdraw as a LEFT JOIN {db_prefix}members AS mem ON (a.id_member = mem.id_member)  WHERE type = {string:type} AND state = {int:state} AND complete = {int:complete} ORDER BY id DESC LIMIT {int:start}, {int:max}',
        array(
            'type' => 'xp',
            'state'=>1,
            'complete'=>0,
            'start' => $limit,
            'max' => $modSettings['defaultMaxMembers'],
        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
        $context['users'][] = $row;
    }

}
function complete(){
    global $scripturl, $context,$smcFunc,$user_info,$modSettings;
    // Make sure they can view the memberlist.
    isAllowedTo(['admin_forum','xp_manage']);

    loadTemplate('ZealyXP');
    $context['sub_template'] = 'complete';
    $context['modify_url'] = $scripturl . '?action=zealy;sa=complete;modify';
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
            $complete = $_POST['complete'];
            if (!empty($complete)){
                $smcFunc['db_query']('', '
					UPDATE {db_prefix}apply_withdraw
					SET complete = {int:complete}
					WHERE id IN ({array_int:id})',
                    array(
                        'complete' => 1,
                        'id' => $complete
                    )
                );
            }
        }
        $_SESSION['adm-save'] = true;
        redirectexit('action=zealy;sa=complete');

    }

    $request = $smcFunc['db_query']('', '
			SELECT COUNT(*)
			FROM {db_prefix}apply_withdraw WHERE type = {string:type} AND complete = {int:complete}',
        array(
            'type' => 'xp',
            'complete'=>1,
        )
    );
    list ($context['num_members']) = $smcFunc['db_fetch_row']($request);
    $smcFunc['db_free_result']($request);
    $_REQUEST['start'] =  $_REQUEST['start']  ?? 0;
    $context['page_index'] = constructPageIndex($scripturl . '?action=zealy;sa=complete', $_REQUEST['start'], $context['num_members'], $modSettings['defaultMaxMembers']);
    $limit = $_REQUEST['start'];
    $context['start'] = $_REQUEST['start'];
    // member-lists
    $request = $smcFunc['db_query']('', '
				SELECT   a.*,mem.member_name,mem.pid,mem.address
			FROM {db_prefix}apply_withdraw as a LEFT JOIN {db_prefix}members AS mem ON (a.id_member = mem.id_member)  WHERE type = {string:type} AND complete = {int:complete} ORDER BY id DESC LIMIT {int:start}, {int:max}',
        array(
            'type' => 'xp',
            'complete'=>1,
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
    isAllowedTo(['admin_forum','xp_manage']);

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
