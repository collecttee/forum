<?php
function ManageMeriter()
{
    global $txt, $context, $scripturl,$smcFunc;
    // Only admins, only EVER admins!
    isAllowedTo('admin_forum');

    // Let's get our things running...
    loadTemplate('ManageMeriter');
//    loadLanguage('Reports');
    $context['page_title'] = $txt['managemeriter_title'];
    $context['post_url'] = $scripturl . '?action=admin;area=managemeriter;save=limit';
    $context['set_url'] = $scripturl . '?action=admin;area=managemeriter;save=set';
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
    $request = $smcFunc['db_query']('', '
			SELECT  id,merit_max_limit
			FROM {db_prefix}smerit_max
			WHERE id = {int:id}
			LIMIT 1',
        array(
            'id' => 1,
        )
    );
    $result = $smcFunc['db_fetch_assoc']($request);
    $context['limit'] = $result['merit_max_limit'] ?? 0;
    // Saving the settings?
    if (isset($_GET['save']))
    {
        checkSession();
        if ($_GET['save'] === 'limit'){
            $limit = $_POST['limit'];
            $request = $smcFunc['db_query']('', '
			SELECT  id
			FROM {db_prefix}smerit_max
			WHERE id = {int:id}
			LIMIT 1',
                array(
                    'id' => 1,
                )
            );
            $result = $smcFunc['db_fetch_assoc']($request);
            if (empty($result)){
                $smcFunc['db_insert']('',
                    '{db_prefix}smerit_max',
                    array(
                        'id' => 'int', 'merit_max_limit' => 'int'
                    ),
                    [1,$limit],
                    array()
                );
            }else{
                $smcFunc['db_query']('', '
					UPDATE {db_prefix}smerit_max
					SET merit_max_limit = {int:merit_max_limit}
					WHERE id = {int:id}',
                    array(
                        'merit_max_limit' => $limit,
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
                redirectexit('action=admin;area=managemeriter');
            }
            $smcFunc['db_insert']('',
                '{db_prefix}member_roles',
                array(
                    'id_member' => 'int',
                    'role_id' => 'int',
                    'create_at' => 'int'
                ),
                [$user_settings['id_member'],1,time()],
                array()
            );
        }
        $_SESSION['adm-save'] = true;
        redirectexit('action=admin;area=managemeriter');
    }

}