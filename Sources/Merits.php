<?php
function SetSourceUser() {
    global $scripturl, $context,$smcFunc;
    // Make sure they can view the memberlist.
    isAllowedTo('view_mlist');

    loadTemplate('Merits');
    $context['post_url'] = $scripturl . '?action=merit;save';
    $context['delete_url'] = $scripturl . '?action=merit';
    if (isset($_POST['work']) && $_POST['work'] == 'delete') {
        checkSession();
        $delete = $_POST['delete'];
        $smcFunc['db_query']('', '
		DELETE FROM {db_prefix}source_user
		WHERE id IN ({array_int:users})',
            array(
                'users' => $delete
            )
        );
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
    // member-lists
    $request = $smcFunc['db_query']('', '
			SELECT  sou.id as id,mem.id_member, mem.member_name,mem.address
			FROM {db_prefix}source_user AS sou
				INNER JOIN {db_prefix}members AS mem ON (sou.id_member = mem.id_member)',
        array(

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
			WHERE id_member = {int:id_member}
			LIMIT 1',
            array(
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
                'create_at' => 'int'
            ),
            [$user_settings['id_member'],time()],
            array()
        );
        $_SESSION['adm-save'] = true;
        redirectexit('action=merit');
    }
}

function smerit(){
    global $scripturl, $context,$smcFunc,$user_info;
    // Make sure they can view the memberlist.
    isAllowedTo('view_mlist');

    loadTemplate('sMerit');
    if (isset($_SESSION['adm-save']))
    {
        if ($_SESSION['adm-save'] === true)
            $context['saved_successful'] = true;
        else
            $context['saved_failed'] = $_SESSION['adm-save'];

        unset($_SESSION['adm-save']);
    }
    $context['post_url'] = $scripturl . '?action=smerit;save';
    // member-lists
    $request = $smcFunc['db_query']('', '
			SELECT  sou.id as id,sou.amount,sou.create_at, mem.member_name
			FROM {db_prefix}smerit_logs AS sou
				INNER JOIN {db_prefix}members AS mem ON (sou.id_member = mem.id_member)',
        array(

        )
    );
    while ($row = $smcFunc['db_fetch_assoc']($request)) {
        $context['users'][] = $row;
    }


    if (isset($_GET['save'])){
        checkSession();
        $amount = $_POST['amount'];
        $request = $smcFunc['db_query']('', '
			SELECT  id_member,amount
			FROM {db_prefix}smerit
			WHERE id_member = {int:id}
			LIMIT 1',
            array(
                'id' => 0,
            )
        );
        $user_settings = $smcFunc['db_fetch_assoc']($request);
        if (empty($user_settings)){
            $smcFunc['db_insert']('',
                '{db_prefix}smerit',
                array(
                    'id_member' => 'int',
                    'amount' => 'int'
                ),
                [0,$amount],
                array()
            );
        } else {
            $smcFunc['db_query']('', '
					UPDATE {db_prefix}smerit
					SET amount = {int:amount}
					WHERE id_member = {int:id}',
                array(
                    'amount' => $user_settings['amount'] + $amount,
                    'id' => 0
                )
            );
        }
        $smcFunc['db_insert']('',
            '{db_prefix}smerit_logs',
            array(
                'id_member' => 'int',
                'amount' => 'int',
                'create_at' => 'int',
            ),
            [$user_info['id'],$amount,time()],
            array()
        );
        $_SESSION['adm-save'] = true;
        redirectexit('action=smerit');
    }

}