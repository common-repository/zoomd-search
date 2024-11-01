<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
if ( ! defined( 'Zoomd_Path' ) ) {
	exit();
}


if(defined( 'Zoomd_DBG' ))
{
	require_once Zoomd_DBG . '/zoomd_debug_pages.php';
	require_once Zoomd_DBG . '/zoomd_debug_constants.php';

}

require_once Zoomd_Path . 'classes/zoomd_constants.php';
require_once Zoomd_Path . 'classes/zoomd_utils.php';
require_once Zoomd_Path . 'classes/Core/HttpClient.php';
require_once Zoomd_Path . 'classes/Core/Settings.php';
require_once Zoomd_Path . 'classes/Core/Logger.php';
require_once Zoomd_Path . 'classes/zoomd_metadata.php';
require_once Zoomd_Path . 'classes/zoomd_settingsView.php';
require_once Zoomd_Path . 'classes/zoomd_registrationView.php';
require_once Zoomd_Path . 'classes/zoomd_widget.php';
require_once Zoomd_Path . 'classes/zoomd_indexer.php';
require_once Zoomd_Path . 'classes/Core/PostUpdater.php';
require_once Zoomd_Path . 'classes/zoomd_post_modifier.php';
require_once Zoomd_Path . 'classes/Core/Activator.php';







//load metadata
zoomd_metadata::BuildMetaInfo();

?>