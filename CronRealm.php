<?php
require_once './vendor/autoload.php';
require_once(dirname(__FILE__) . '/Settings.php');
use Web3\Contract;
use GuzzleHttp\Client as GuzzleHttp;
define('SMF', 1);
define('SMF_VERSION', '2.1.3');
define('SMF_FULL_VERSION', 'SMF ' . SMF_VERSION);
define('SMF_SOFTWARE_YEAR', '2022');

define('JQUERY_VERSION', '3.6.0');
define('POSTGRE_TITLE', 'PostgreSQL');
define('MYSQL_TITLE', 'MySQL');
define('SMF_USER_AGENT', 'Mozilla/5.0 (' . php_uname('s') . ' ' . php_uname('m') . ') AppleWebKit/605.1.15 (KHTML, like Gecko)  SMF/' . strtr(SMF_VERSION, ' ', '.'));
define('RPC_URL','https://eptest.wizz.cash/proxy/');
define('PARENT_REALM_ID','7189eceba798711469728fcd197f651f9d04ef3bb445894446ed7063e5d4a803i0');
set_time_limit(0);
$block = file_get_contents("lastblock");
$currentBlock = file_get_contents("https://mempool.space/testnet/api/blocks/tip/height");

$smcFunc = array();
getXP();
function getXP(){
    global  $modSettings, $sourcedir;
    require_once($sourcedir . '/Load.php');
    require_once($sourcedir . '/Subs-Members.php');
    require_once($sourcedir . '/Subs.php');
    require_once($sourcedir . '/Security.php');
    require_once($sourcedir . '/Logging.php');
    require_once($sourcedir . '/Errors.php');
    $client = new GuzzleHttp(array_merge(['timeout' => 60, 'verify' => false]));
    try {
        $subRealms = request($client,'blockchain.atomicals.find_subrealms', PARENT_REALM_ID);
        foreach ($subRealms as $subRealm) {
            $val = [];
            $result = request($client,'blockchain.atomicals.get_location', $subRealm->atomical_id);
            $hexScript = $result->location_info[0]->script;
            $command = "node ./node-verify-message/script-address.js  {$hexScript}";
            exec($command, $val, $err);
            if (isset($val[0]) && strlen($val[0]) == 62) {

            }

        }
        sleep(2);
    } catch (Exception $exception) {
        echo 'error:'.$exception->getMessage().PHP_EOL;
    }

}
function updateXp($address,$xp){
    global $smcFunc;
    loadDatabase();
    reloadSettings();
    $request = $smcFunc['db_query']('', '
		SELECT address
		FROM {db_prefix}user_xp
		WHERE address = {string:address}
		LIMIT 1',
        array(
            'address' => $address,
        )
    );
    // @todo Separate the sprintf?
    if ($smcFunc['db_num_rows']($request) != 0){
        $smcFunc['db_query']('', '
					UPDATE {db_prefix}user_xp
					SET xp = {int:xp},update_time = {int:time}
					WHERE address = {string:address}',
            array(
                'time' => time(),
                'address' => $address,
                'xp' => $xp
            )
        );
    }else{
        $smcFunc['db_insert']('',
            '{db_prefix}user_xp',
            array(
                'address' => 'string',
                'xp' => 'int',
                'update_time' => 'int'
            ),
            [$address,$xp,time()],
            array()
        );
    }

}
