<?php

use Zoomd\Core\Settings;
use Zoomd\Core\Activator;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

 if(Settings::isValid()) 
    Activator::registerEvents();
else
    Activator::unRegisterEvents();


?>