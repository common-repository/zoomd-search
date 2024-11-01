<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
    class zoomd_metadata{

        public static $authors;
        public static function BuildMetaInfo()
        {
            $users = get_users();
            
            foreach ($users as $user) 
            {
                zoomd_metadata::$authors[$user->ID]  = $user->display_name; 
            }            
        }
    }
?>