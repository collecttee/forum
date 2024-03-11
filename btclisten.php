<?php
require_once './vendor/autoload.php';
require_once(dirname(__FILE__) . '/Settings.php');
use GuzzleHttp\Client as GuzzleHttp;
//use BitWasp\Bitcoin\Bitcoin;
define('SMF', 1);
define('SMF_VERSION', '2.1.3');
define('SMF_FULL_VERSION', 'SMF ' . SMF_VERSION);
define('SMF_SOFTWARE_YEAR', '2022');

define('JQUERY_VERSION', '3.6.0');
define('POSTGRE_TITLE', 'PostgreSQL');
define('MYSQL_TITLE', 'MySQL');
define('SMF_USER_AGENT', 'Mozilla/5.0 (' . php_uname('s') . ' ' . php_uname('m') . ') AppleWebKit/605.1.15 (KHTML, like Gecko)  SMF/' . strtr(SMF_VERSION, ' ', '.'));
$smcFunc = array();
//$rpcUrl = 'https://goerli.infura.io/v3/'. $apiKey;
//$rpcUrl = 'https://arbitrum-goerli.infura.io/v3/'. $apiKey;
define('RPC_URL','https://eptest.wizz.cash/proxy/');
define('PARENT_REALM_ID','7189eceba798711469728fcd197f651f9d04ef3bb445894446ed7063e5d4a803i0');
$client = new GuzzleHttp(array_merge(['timeout' => 60, 'verify' => false]));
while (true){
    try {
        $subRealms = request($client,'blockchain.atomicals.find_subrealms', PARENT_REALM_ID);
        foreach ($subRealms as $subRealm) {
            $val = [];
            $result = request($client,'blockchain.atomicals.get_location', $subRealm->atomical_id);
            $hexScript = $result->location_info[0]->script;
            $command = "node ./node-verify-message/script-address.js  {$hexScript}";
            exec($command, $val, $err);
            if (isset($val[0]) && strlen($val[0]) == 62) {
                $ret = Register(0,$result->{'$request_subrealm'}.time(),$val[0],'');
                echo json_encode($ret);
                echo PHP_EOL;
            }

        }
        sleep(2);
    } catch (Exception $exception) {
        echo 'error:'.$exception->getMessage().PHP_EOL;
    }

}

function request($client,$method, $params){
    $params = '?params=["' . $params . '"]';
    $url = RPC_URL.$method.$params;
    $res = $client->get($url);

    $body = json_decode($res->getBody());

    if (isset($body->error) && !empty($body->error)) {
        throw new \Exception($body->error->message . " [Method] {$method}", $body->error->code);
    }
    return $body->response->result;
}
function Register($pid,$user,$address,$email){
    global  $modSettings, $sourcedir;
    require_once($sourcedir . '/Load.php');
    global $smcFunc;
    require_once($sourcedir . '/Subs-Members.php');
    require_once($sourcedir . '/Subs.php');
    require_once($sourcedir . '/Security.php');
    require_once($sourcedir . '/Logging.php');
    require_once($sourcedir . '/Errors.php');
    loadDatabase();
    reloadSettings();
    $regOptions = array(
        'interface' => 'guest',
        'username' => $user,
        'btcaddress' =>  $address,
        'address' =>  '',
        'email' => $email,
        'pid'=>$pid,
        'password' => '',
        'password_check' => '',
        'check_reserved_name' => true,
        'check_password_strength' => true,
        'check_email_ban' => true,
        'send_welcome_email' => false,
        'require' => 'nothing',
        'extra_register_vars' => array(),
        'theme_vars' => array(),
    );
    $request = $smcFunc['db_query']('', '
		SELECT id_member
		FROM {db_prefix}members
		WHERE member_name = {string:username}
			OR address = {string:address}
		LIMIT 1',
        array(
            'address' => $address,
            'username' => $user,
        )
    );
    // @todo Separate the sprintf?
    if ($smcFunc['db_num_rows']($request) != 0){
        return ['status'=>0,'msg'=>'exists','account'=>$user,'address'=>$address];
    }
    $memberID = registerMember($regOptions, true,true);
    if (!is_array($memberID)) {
       return ['status'=>0,'msg'=>'Ok','account'=>$user,'address'=>$address];
    }else{
        return ['status'=>0,'msg'=>$memberID,'account'=>$user,'address'=>$address];
    }

}
