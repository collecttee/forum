<?php
// Load the ecrecover...

function Sign() {
   $res =  doSign();
   echo json_encode($res);die;
}
function btcSign() {
    $res =  dobtcSign();
    echo json_encode($res);die;
}
function initializePassword(){
    global  $modSettings, $sourcedir,$user_info;
    global $smcFunc;
    require_once($sourcedir . '/Subs-Members.php');
    if (empty($user_info)){
        echo json_encode(['status'=>0,'msg'=>'This user does not exist']);die;
    }
    if ($_POST['password'] != $_POST['password2']){
        echo json_encode(['status'=>0,'msg'=>'The passwords entered twice are inconsistent']);die;
    }
    $password = $_POST['password'];

    $passwd =  hash_password($user_info['username'], $password);
    $ret = $smcFunc['db_query']('', '
					UPDATE {db_prefix}members
					SET passwd = {string:passwd},
					initialize_password = 1
					WHERE id_member = {int:id}',
        array(
            'passwd' => $passwd,
            'id' => $user_info['id'],
        )
    );
    if ($ret){
        echo json_encode(['status'=>1,'msg'=>'ok']);die;
    }else{
        echo json_encode(['status'=>0,'msg'=>'operation failed']);die;
    }
}
function dobtcSign(){
    global $smcFunc,$sourcedir,$user_settings;
    if(isset($_REQUEST['action']) && in_array($_REQUEST['action'],array('btcsign'))) {
//        $message =  'Sign in to this forum, your data is secure!';
        $signed = $_REQUEST['sign'] ?? "";
        $pubkey = $_REQUEST['pubkey'] ?? "";
        $command = "node ./node-verify-message/index.js  {$signed} {$pubkey}";
        exec($command, $val, $err);
        if ($err == 0 && $val[0] != "error") {
            require_once($sourcedir .'/LogInOut.php');
            $request = $smcFunc['db_query']('', '
			SELECT  address,passwd, id_member, id_group, lngfile, is_activated, email_address, additional_groups, member_name, password_salt,
				passwd_flood, tfa_secret
			FROM {db_prefix}members
			WHERE btcaddress = {string:btcaddress}
			LIMIT 1',
                array(
                    'btcaddress' => $val[0],
                )
            );
            $user_settings = $smcFunc['db_fetch_assoc']($request);
            if (!empty($user_settings)) {
                DoLogin('firedao');
            }else{
                if (isset($_REQUEST['verify']) && $_REQUEST['verify'] == 1) {
                    return array('status'=>1,'error'=>'sign success');
                } else {
                    return array('status'=>0,'error'=>'not found this address');
                }

            }
        }else{
            return array('status'=>0,'error'=>'sign failed');
        }
    }
}
function doSign(){
    global $smcFunc,$boarddir,$sourcedir,$user_settings;
    require_once($boarddir . '/Ecrecover.php');
    if(isset($_REQUEST['action']) && in_array($_REQUEST['action'],array('sign','daoregister'))) {
        $time  = time();
        $sortanow = $time - ($time % 600);
        $address = $_REQUEST['address'] ?? "";
        $message =  'Signning in to firedao' . 'at' . $sortanow;
        $signed = $_REQUEST['sign'] ?? "";
        if (strtolower($address) == personal_ecRecover($message, $signed)) {
            require_once($sourcedir .'/LogInOut.php');
            $request = $smcFunc['db_query']('', '
			SELECT  address,passwd, id_member, id_group, lngfile, is_activated, email_address, additional_groups, member_name, password_salt,
				passwd_flood, tfa_secret
			FROM {db_prefix}members
			WHERE address = {string:address}
			LIMIT 1',
                array(
                    'address' => $address,
                )
            );
            $user_settings = $smcFunc['db_fetch_assoc']($request);
            if (!empty($user_settings)) {
                DoLogin('firedao');
            }else{
                if (isset($_REQUEST['verify']) && $_REQUEST['verify'] == 1) {
                    return array('status'=>1,'error'=>'sign success');
                } else {
                    return array('status'=>0,'error'=>'not found this address');
                }

            }
        }else{
            return array('status'=>0,'error'=>'sign failed');
        }
    }
}

function Register(){
    global  $modSettings, $sourcedir;
    global $smcFunc;
    require_once($sourcedir . '/Subs-Members.php');
    $ret = doSign();
    if ($ret['status'] == 0) {
        echo json_encode($ret);die;
    }
    $regOptions = array(
        'interface' => 'guest',
        'username' => !empty($_POST['user']) ? $_POST['user'] : '',
        'address' =>  !empty($_POST['address']) ? $_POST['address'] : '',
        'is_activated' => 1,
        'email' => !empty($_POST['email']) ? $_POST['email'] : '',
        'password' => !empty($_POST['passwrd1']) ? $_POST['passwrd1'] : 'default',
        'password_check' => !empty($_POST['passwrd2']) ? $_POST['passwrd2'] : 'default',
        'check_reserved_name' => true,
        'check_password_strength' => true,
        'check_email_ban' => true,
        'send_welcome_email' => !empty($modSettings['send_welcomeEmail']),
        'require' => 'nothing',
        'extra_register_vars' => array(),
        'theme_vars' => array(),
    );
    $memberID = registerMember($regOptions, true,true);
    if (!is_array($memberID)) {
        echo json_encode(array('status'=>1,'error'=>'register success'));die;
    }else{
        echo json_encode(array('status'=>0,'error'=>'register failed'));die;
    }

}
