<?php
require_once './vendor/autoload.php';
require_once(dirname(__FILE__) . '/Settings.php');
use Web3\Contract;
use GuzzleHttp\Client as GuzzleHttp;
define('SMF', 1);
define('SMF_VERSION', '2.1.2');
define('SMF_FULL_VERSION', 'SMF ' . SMF_VERSION);
define('SMF_SOFTWARE_YEAR', '2022');

define('JQUERY_VERSION', '3.6.0');
define('POSTGRE_TITLE', 'PostgreSQL');
define('MYSQL_TITLE', 'MySQL');
define('SMF_USER_AGENT', 'Mozilla/5.0 (' . php_uname('s') . ' ' . php_uname('m') . ') AppleWebKit/605.1.15 (KHTML, like Gecko)  SMF/' . strtr(SMF_VERSION, ' ', '.'));
$smcFunc = array();
$rpcUrl = 'https://goerli.infura.io/v3/a2705a79f1da451ca9683d095bd5d87f';
$contractAddress = '0xEF30E79a5F209cd5417b6a9B246D411685fc0BB5';
$client = new GuzzleHttp(array_merge(['timeout' => 60, 'verify' => false], ['base_uri' => $rpcUrl]));
$abi = file_get_contents("user.json");
$contract = new Contract($rpcUrl,$abi);
$contractAbi  = $contract->getEthabi();
while (true){
    try {
        $block = request($client,'eth_blockNumber', []);
        $data = ['fromBlock'=>'0x0','toBlock'=>$block,'address'=>$contractAddress];
        $res = request($client,'eth_getLogs', [$data]);
        if (!empty($res)){
            foreach ($res as $val){
                $ret = $contractAbi->decodeParameters(['uint256','string','address','string','uint256'],$val->data);
                if (!empty($ret)){
                    $ret = Register($ret[1],$ret[2],$ret[3]);
                    echo json_encode($ret);
                    echo PHP_EOL;
                }
            }
        }
        sleep(5);
    } catch (Exception $exception) {
        echo $exception->getMessage();
    }
}

function request($client,$method, $params = []){

    $data = [
        'json' => [
            'jsonrpc' => '2.0',
            'method' => $method,
            'params' => $params,
            'id' => 0,
        ]
    ];
    $res = $client->post('', $data);
    $body = json_decode($res->getBody());
    if (isset($body->error) && !empty($body->error)) {
        throw new \Exception($body->error->message . " [Method] {$method}", $body->error->code);
    }
    return $body->result;
}
function Register($user,$address,$email){
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
        'address' =>  $address,
        'email' => $email,
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
