<?php
require_once './vendor/autoload.php';
require_once 'index.php';
use Web3\Contract;
use GuzzleHttp\Client as GuzzleHttp;
$rpcUrl = 'https://goerli.infura.io/v3/a2705a79f1da451ca9683d095bd5d87f';
$contractAddress = '0x683fc3657036720a89e2695ab16fc9ae018299da';
$client = new GuzzleHttp(array_merge(['timeout' => 60, 'verify' => false], ['base_uri' => $rpcUrl]));
$abi = file_get_contents("user.json");
$contract = new Contract($rpcUrl,$abi);
$contractAbi  = $contract->getEthabi();
$block = request($client,'eth_blockNumber', []);

$data = ['fromBlock'=>'0x0','toBlock'=>$block,'address'=>$contractAddress];
$res = request($client,'eth_getLogs', [$data]);
$a = $contractAbi->decodeParameters(['uint256','string','address','uint256'],$res[0]->data);
var_dump($a);die;

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
function Register($user,$address){
    global  $modSettings, $sourcedir;
    global $smcFunc;
    require_once($sourcedir . '/Subs-Members.php');
    $regOptions = array(
        'interface' => 'guest',
        'username' => $user,
        'address' =>  $address,
        'email' => '',
        'password' => '',
        'password_check' => '',
        'check_reserved_name' => true,
        'check_password_strength' => true,
        'check_email_ban' => true,
        'send_welcome_email' => !empty($modSettings['send_welcomeEmail']),
        'require' => !empty($modSettings['coppaAge']) && empty($_SESSION['skip_coppa']) ? 'coppa' : (empty($modSettings['registration_method']) ? 'nothing' : ($modSettings['registration_method'] == 1 ? 'activation' : 'approval')),
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
