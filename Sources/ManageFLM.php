<?php
function ManageFLM()
{
    global $txt, $context, $scripturl,$smcFunc;
    // Only admins, only EVER admins!
    isAllowedTo('admin_forum');

    // Let's get our things running...
    loadTemplate('ManageFLM');

//    loadLanguage('Reports');
    $context['page_title'] = 'manageflm';
    $context['post_url'] = $scripturl . '?action=admin;area=manageflm;save=limit';
    $context['set_url'] = $scripturl . '?action=admin;area=manageflm;save=set';
    $context['pause_url'] = $scripturl . '?action=admin;area=manageflm;save=pause';
    $context['delete_url'] = $scripturl . '?action=admin;area=manageflm';
    if (isset($_POST['work']) && $_POST['work'] == 'delete') {
        checkSession();
        $delete = $_POST['delete'];
        $smcFunc['db_query']('', '
		DELETE FROM {db_prefix}member_roles
		WHERE id IN ({array_int:users}) AND role_id = {int:role_id}',
            array(
                'users' => $delete,
                'role_id' => 2
            )
        );
        redirectexit('action=admin;area=manageflm');
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
    $context['limit'] = $result['flm_max_limit'] ?? 0;

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
    $context['pause'] = $result['flm_max_limit'] ?? 0;
    // member-lists
    $request = $smcFunc['db_query']('', '
			SELECT  rol.id as id,mem.id_member, mem.member_name,mem.address
			FROM {db_prefix}member_roles AS rol
				INNER JOIN {db_prefix}members AS mem ON (rol.id_member = mem.id_member)
			WHERE rol.role_id = {int:role_id}',
        array(
            'role_id' => 2
        )
    );

    while ($row = $smcFunc['db_fetch_assoc']($request)) {
        $context['users'][] = $row;
    }

    $smcFunc['db_free_result']($request);


    // Saving the settings?
    if (isset($_GET['save']))
    {
        checkSession();
        if ($_GET['save'] === 'limit'){
            $limit = $_POST['limit'];
            greaterThan($limit,0);
            $request = $smcFunc['db_query']('', '
			SELECT  id
			FROM {db_prefix}property_max
			WHERE id = {int:id}
			LIMIT 1',
                array(
                    'id' => 1,
                )
            );
            $result = $smcFunc['db_fetch_assoc']($request);
            if (empty($result)){
                $smcFunc['db_insert']('',
                    '{db_prefix}property_max',
                    array(
                        'id' => 'int', 'flm_max_limit' => 'int'
                    ),
                    [1,$limit],
                    array()
                );
            }else{
                $smcFunc['db_query']('', '
					UPDATE {db_prefix}property_max
					SET flm_max_limit = {int:flm_max_limit}
					WHERE id = {int:id}',
                    array(
                        'flm_max_limit' => $limit,
                        'id' => 1
                    )
                );
            }
        }
        if ($_GET['save'] === 'set') {
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
                redirectexit('action=admin;area=manageflm');
            }
            $request = $smcFunc['db_query']('', '
			SELECT  id_member
			FROM {db_prefix}member_roles
			WHERE id_member = {int:id_member}
			AND role_id = {int:role_id}
			LIMIT 1',
                array(
                    'id_member' => $user_settings['id_member'],
                    'role_id' => 2
                )
            );
            $exists = $smcFunc['db_fetch_assoc']($request);
            if (!empty($exists)){
                $_SESSION['exists'] = true;
                redirectexit('action=admin;area=manageflm');
            }
            $smcFunc['db_insert']('',
                '{db_prefix}member_roles',
                array(
                    'id_member' => 'int',
                    'role_id' => 'int',
                    'create_at' => 'int'
                ),
                [$user_settings['id_member'],2,time()],
                array()
            );
        }
        if ($_GET['save'] === 'pause') {
            if (isset($_POST['pause']) && $_POST['pause'] == 1){
                $request = $smcFunc['db_query']('', '
                    SELECT  id
                    FROM {db_prefix}property_max
                    WHERE id = {int:id}
                    LIMIT 1',
                    array(
                        'id' => 2,
                    )
                );
                $result = $smcFunc['db_fetch_assoc']($request);
                if (empty($result)){
                    $smcFunc['db_insert']('',
                        '{db_prefix}property_max',
                        array(
                            'id' => 'int', 'flm_max_limit' => 'int'
                        ),
                        [2,1],
                        array()
                    );
                }else{
                    $smcFunc['db_query']('', '
					UPDATE {db_prefix}property_max
					SET flm_max_limit = {int:flm_max_limit}
					WHERE id = {int:id}',
                        array(
                            'flm_max_limit' => 1,
                            'id' => 2
                        )
                    );
                }
            }else{
                $smcFunc['db_query']('', '
					UPDATE {db_prefix}property_max
					SET flm_max_limit = {int:flm_max_limit}
					WHERE id = {int:id}',
                    array(
                        'flm_max_limit' => 0,
                        'id' => 2
                    )
                );
            }
        }
        $_SESSION['adm-save'] = true;
        redirectexit('action=admin;area=manageflm');
    }

}