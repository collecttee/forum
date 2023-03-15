<?php
function SendMain(){
    global $user_info,$context,$smcFunc,$scripturl;
    loadTemplate('Send');
    $title = $_GET['title'] ?? '';
    $msg = $_GET['message_id'] ?? '';
    $topic = $_GET['topic_id'] ?? '';
    $context['title'] = $title;
    $context['msg'] = $msg;
    $context['topic'] = $topic;
    $context['username'] = $user_info['username'];
    $context['post_url'] = $scripturl . '?action=send;save';
    $request = $smcFunc['db_query']('', '
                    SELECT  smerit,merit
                    FROM {db_prefix}property
                    WHERE id_member = {int:id}
                    LIMIT 1',
        array(
            'id' => $user_info['id'],
        )
    );
    if (isset($_SESSION['adm-save']))
    {
        if ($_SESSION['adm-save'] === true)
            $context['saved_successful'] = true;
        else
            $context['saved_failed'] = $_SESSION['adm-save'];

        unset($_SESSION['adm-save']);
    }
    $pool = $smcFunc['db_fetch_assoc']($request);

    $smcFunc['db_free_result']($request);
    $context['smerit_amount']  = $pool['smerit'];
    $context['merit_amount']  = $pool['merit'];
    if (isset($_SESSION['not-found']))
    {
        if ($_SESSION['not-found'] === true)
            $context['not_found_user'] = true;
        else
            $context['saved_failed'] = $_SESSION['adm-save'];

        unset($_SESSION['not-found']);
    }

    if (isset($_GET['save']))
    {
        checkSession();
        $topic= $_POST['topic'] ?? 0;
        $msg = $_POST['msg'] ?? 0;
        $amount = $_POST['amount'] ?? 0;
        $request = $smcFunc['db_query']('', '
			SELECT  id_member,subject
			FROM {db_prefix}messages
			WHERE id_msg = {int:msg}
			AND id_topic = {int:topic}
			LIMIT 1',
            array(
                'msg' => $msg,
                'topic' => $topic
            )
        );
        $message_ret = $smcFunc['db_fetch_assoc']($request);
        if (empty($message_ret)){
            $_SESSION['not-found'] = true;
            redirectexit('action=send');
        }
        $request = $smcFunc['db_query']('', '
			SELECT  id_member,smerit
			FROM {db_prefix}property
			WHERE id_member = {int:id}
			LIMIT 1',
            array(
                'id' => $user_info['id'],
            )
        );
        $ret = $smcFunc['db_fetch_assoc']($request);

        if ($ret['smerit'] < $amount) {
            $_SESSION['not-found'] = true;
            redirectexit('action=send');
        }
        $mintAmount = floor($amount / 2);
        $ceil = $amount % 2;
        $request = $smcFunc['db_query']('', '
			SELECT  id_member,smerit,merit
			FROM {db_prefix}property
			WHERE id_member = {int:to}
			LIMIT 1',
            array(
                'to' => $message_ret['id_member'],
            )
        );

        $toRet = $smcFunc['db_fetch_assoc']($request);
        if (empty($toRet)){
            $smcFunc['db_insert']('',
                '{db_prefix}property',
                array(
                    'id_member' => 'int',
                    'smerit' => 'int',
                    'merit' => 'int',
                ),
                [$message_ret['id_member'],$mintAmount,$amount],
                array()
            );
        } else {
            if ($toRet['merit'] % 2 > 0 && $ceil > 0){
                $mintAmount+=1;
            }
            $smcFunc['db_query']('', '
			UPDATE {db_prefix}property
			SET smerit = {int:smerit},
			merit = {int:merit}
			WHERE id_member = {int:to}',
                array(
                    'to' => $message_ret['id_member'],
                    'smerit' => $toRet['smerit'] + $mintAmount,
                    'merit' => $toRet['merit'] + $amount
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
            [$message_ret['id_member'],$mintAmount,0,time()],
            array()
        );
        $smcFunc['db_query']('', '
			UPDATE {db_prefix}property
			SET smerit = {int:smerit}
			WHERE id_member = {int:from}',
            array(
                'from' => $user_info['id'],
                'smerit' => $ret['smerit'] - $amount,
            )
        );
        $smcFunc['db_insert']('',
            '{db_prefix}sender_merit',
            array(
                'id_topic' => 'int',
                'id_msg' => 'int',
                'id_member' => 'int',
                'amount' => 'int',
                'create_at' => 'int',
            ),
            [$topic,$msg,$user_info['id'],$amount,time()],
            array()
        );


        $_SESSION['adm-save'] = true;
        redirectexit("action=send&message_id={$msg}&topic_id={$topic}&title={$message_ret['subject']}");
    }
}