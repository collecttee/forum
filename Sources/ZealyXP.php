<?php
function ManageXP()
{
    global $txt, $context, $scripturl,$smcFunc;
    // Only admins, only EVER admins!
    isAllowedTo('admin_forum');

    // Let's get our things running...
    loadTemplate('ManageXP');

//    loadLanguage('Reports');
    $context['page_title'] = 'ManageXP';
    $context['post_url'] = $scripturl . '?action=admin;area=zealyXP;save=limit';
    $context['set_url'] = $scripturl . '?action=admin;area=zealyXP;save=set';
    $context['radio_url'] = $scripturl . '?action=admin;area=zealyXP;save=radio';
    $context['pause_url'] = $scripturl . '?action=admin;area=zealyXP;save=pause';
    $context['delete_url'] = $scripturl . '?action=admin;area=zealyXP';
    if (isset($_POST['work']) && $_POST['work'] == 'delete') {
        checkSession();
        $delete = $_POST['delete'];
        $smcFunc['db_query']('', '
		DELETE FROM {db_prefix}member_roles
		WHERE id IN ({array_int:users}) AND role_id = {int:role_id}',
            array(
                'users' => $delete,
                'role_id' => 3
            )
        );
        redirectexit('action=admin;area=zealyXP');
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
    $context['pause'] = $result['pause'] ?? 0;
    $context['limit'] = $result['min'] ?? 0;
    $context['max'] = $result['max'] ?? 0;
    $context['radio'] = $result['radio'] ?? 0;
    // member-lists
    $request = $smcFunc['db_query']('', '
			SELECT  rol.id as id,mem.id_member, mem.member_name,mem.address
			FROM {db_prefix}member_roles AS rol
				INNER JOIN {db_prefix}members AS mem ON (rol.id_member = mem.id_member)
			WHERE rol.role_id = {int:role_id}',
        array(
            'role_id' => 3
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
            $min = $_POST['min'];
            $max= $_POST['max'];
            greaterThan($min,0);
            greaterThan($max,$min);
            $request = $smcFunc['db_query']('', '
			SELECT  id
			FROM {db_prefix}zealy_config
			WHERE id = {int:id}
			LIMIT 1',
                array(
                    'id' => 1,
                )
            );
            $result = $smcFunc['db_fetch_assoc']($request);
            if (empty($result)){
                $smcFunc['db_insert']('',
                    '{db_prefix}zealy_config',
                    array(
                        'id' => 'int', 'min' => 'int', 'max' => 'int'
                    ),
                    [1,$min,$max],
                    array()
                );
            }else{
                $smcFunc['db_query']('', '
					UPDATE {db_prefix}zealy_config
					SET min = {int:min},
					max = {int:max}
					WHERE id = {int:id}',
                    array(
                        'min' => $min,
                        'max' => $max,
                        'id' => 1
                    )
                );
            }
        }
        if ($_GET['save'] === 'radio'){
            $radio = $_POST['radio'];
            greaterThan($radio,0);
            $request = $smcFunc['db_query']('', '
			SELECT  id
			FROM {db_prefix}zealy_config
			WHERE id = {int:id}
			LIMIT 1',
                array(
                    'id' => 1,
                )
            );
            $result = $smcFunc['db_fetch_assoc']($request);
            if (empty($result)){
                $smcFunc['db_insert']('',
                    '{db_prefix}zealy_config',
                    array(
                        'id' => 'int', 'radio' => 'int',
                    ),
                    [1,$radio],
                    array()
                );
            }else{
                $smcFunc['db_query']('', '
					UPDATE {db_prefix}zealy_config
					SET radio = {int:radio}
					WHERE id = {int:id}',
                    array(
                        'radio' => $radio,
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
                redirectexit('action=admin;area=zealyXP');
            }
            $request = $smcFunc['db_query']('', '
			SELECT  id_member
			FROM {db_prefix}member_roles
			WHERE id_member = {int:id_member}
			AND role_id = {int:role_id}
			LIMIT 1',
                array(
                    'id_member' => $user_settings['id_member'],
                    'role_id' => 3 // 2 FLM Manager 3 XP Manager
                )
            );
            $exists = $smcFunc['db_fetch_assoc']($request);
            if (!empty($exists)){
                $_SESSION['exists'] = true;
                redirectexit('action=admin;area=zealyXP');
            }
            $smcFunc['db_insert']('',
                '{db_prefix}member_roles',
                array(
                    'id_member' => 'int',
                    'role_id' => 'int',
                    'create_at' => 'int'
                ),
                [$user_settings['id_member'],3,time()],
                array()
            );
        }
        if ($_GET['save'] === 'pause') {
            if (isset($_POST['pause']) && $_POST['pause'] == 1){
                $request = $smcFunc['db_query']('', '
                    SELECT  id
                    FROM {db_prefix}zealy_config
                    WHERE id = {int:id}
                    LIMIT 1',
                    array(
                        'id' => 1,
                    )
                );
                $result = $smcFunc['db_fetch_assoc']($request);
                if (empty($result)){
                    $smcFunc['db_insert']('',
                        '{db_prefix}zealy_config',
                        array(
                            'id' => 'int', 'pause' => 'int'
                        ),
                        [1,1],
                        array()
                    );
                }else{
                    $smcFunc['db_query']('', '
					UPDATE {db_prefix}zealy_config
					SET pause = {int:pause}
					WHERE id = {int:id}',
                        array(
                            'pause' => 1,
                            'id' => 1
                        )
                    );
                }
            }else{
                $smcFunc['db_query']('', '
					UPDATE {db_prefix}zealy_config
					SET pause = {int:pause}
					WHERE id = {int:id}',
                    array(
                        'pause' => 0,
                        'id' => 1
                    )
                );
            }
        }
        $_SESSION['adm-save'] = true;
        redirectexit('action=admin;area=zealyXP');
    }

}
