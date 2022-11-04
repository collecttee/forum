<?php
// Load the ecrecover...

function Sign() {
    global $smcFunc,$boarddir,$sourcedir,$user_settings;
    require_once($boarddir . '/Ecrecover.php');
    if(isset($_REQUEST['action']) && $_REQUEST['action'] === 'sign') {
        $time  = time();
        $sortanow = $time - ($time % 600);
        $address = $_REQUEST['address'] ?? "";
        $message =  'Signning in to ' . $_SERVER['SERVER_NAME'] . 'at' . $sortanow;
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
                DoLogin();
            }else{
                if (isset($_REQUEST['verify']) && $_REQUEST['verify'] == 1) {
                    $_SESSION['sign_address'] = $address;
                    echo json_encode(array('status'=>1,'error'=>'sign success'));die;
                } else {
                    echo json_encode(array('status'=>0,'error'=>'not found this address'));die;
                }

            }
        }else{
            echo json_encode(array('status'=>0,'error'=>'sign failed'));die;
        }
    }
}

function Register(){
    global  $modSettings, $sourcedir;
    global $smcFunc;
    require_once($sourcedir . '/Subs-Members.php');
    if (!isset($_SESSION['sign_address']) || empty($_SESSION['sign_address'])) {
        echo 'please sign this account' ; die;
    }
    $regOptions = array(
        'interface' => 'guest',
        'username' => !empty($_POST['user']) ? $_POST['user'] : '',
        'address' =>  $_SESSION['sign_address'],
        'email' => !empty($_POST['email']) ? $_POST['email'] : '',
        'password' => !empty($_POST['passwrd1']) ? $_POST['passwrd1'] : 'default',
        'password_check' => !empty($_POST['passwrd2']) ? $_POST['passwrd2'] : 'default',
        'check_reserved_name' => true,
        'check_password_strength' => true,
        'check_email_ban' => true,
        'send_welcome_email' => !empty($modSettings['send_welcomeEmail']),
        'require' => !empty($modSettings['coppaAge']) && empty($_SESSION['skip_coppa']) ? 'coppa' : (empty($modSettings['registration_method']) ? 'nothing' : ($modSettings['registration_method'] == 1 ? 'activation' : 'approval')),
        'extra_register_vars' => array(),
        'theme_vars' => array(),
    );
    $memberID = registerMember($regOptions, true,true);
    var_dump($memberID);die;

}
