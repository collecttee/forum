<?php
require_once './vendor/autoload.php';
use Web3\Contract;
use GuzzleHttp\Client as GuzzleHttp;
$rpcUrl = 'https://goerli.infura.io/v3/a2705a79f1da451ca9683d095bd5d87f';
$contractAddress = '0x683fc3657036720a89e2695ab16fc9ae018299da';
$client = new GuzzleHttp(array_merge(['timeout' => 60, 'verify' => false], ['base_uri' => $rpcUrl]));
$abi = file_get_contents("user.json");
$contract = new Contract($rpcUrl,$abi);
$contractAbi  = $contract->getEthabi();
$a = $contractAbi->decodeParameters(['uint256','string','address'],'0x00000000000000000000000000000000000000000000000000000000000000020000000000000000000000000000000000000000000000000000000000000080000000000000000000000000210488503ed19f256052ec43de60b06b9a47a592000000000000000000000000000000000000000000000000000000006371c15400000000000000000000000000000000000000000000000000000000000000065452455452450000000000000000000000000000000000000000000000000000');
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
