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
set_time_limit(0);
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
    global $user_info,$zealySubdomain,$zealyLeaderboardApiKey;
    $ret  = curlGet("https://api.zealy.io/communities/{$zealySubdomain}/leaderboard?limit=1000&page=0",[],["x-api-key:{$zealyLeaderboardApiKey}"]);
    $ret = json_decode($ret,1);
    for ($i = 0;$i<=$ret['totalPages'];$i++){
        $res  = curlGet("https://api.zealy.io/communities/{$zealySubdomain}/leaderboard?limit=1000&page={$i}",[],["x-api-key:{$zealyLeaderboardApiKey}"]);
        $res = json_decode($res,1);
        if (!empty($res['leaderboard'])){
            foreach ($res['leaderboard'] as $row) {
                updateXp(strtolower($row['address']),$row['xp']);
            }
        }
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
                'update_time' => time(),
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
