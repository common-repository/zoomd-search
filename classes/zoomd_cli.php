<?php

use Zoomd\Core\Logger;

class zoomd_cli extends \WP_CLI_Command {

    protected $logger;
    protected $indexer;

	public function __construct(){
        $this->logger = new Logger();
        $this->indexer = new zoomd_indexer();
    }

	
	/**
	 * Index site.
	 * 
	 * ## EXAMPLES
	 *
	 *     wp zoomd index
	 *
	 * @alias index
	 */
	public function index() {        
		$this->logger->Info("wp_cli - index called");        
        $this->indexer->index();
        echo 'Done';
	}

     /**
	 * Reindex site.
	 * 
	 * ## EXAMPLES
	 *
	 *     wp zoomd reindex
	 *
	 * @alias reindex
	 */
    public function reindex() {        
		$this->logger->Info("wp_cli - reindex called");
        delete_option(LASTINDEXED);
        $this->indexer->index();
        echo 'Done';
	}

    /**
	 * Change log level to Info.
	 * 
	 * ## EXAMPLES
	 *
	 *     wp zoomd loglevel 1 
	 *
	 * @alias loglevel
	 */
    public function loglevel($newlevel) {        		
		if(isset($newlevel)) 
		{
			if(!empty($newlevel))
			{					
				$numofhours = 3;		
				$newlevelarg = 	$newlevel[0];
				if(isset($newlevelarg) && is_numeric($newlevelarg))
					$numofhoursarg = $newlevel[1];
					if(isset($numofhoursarg) && is_numeric($numofhoursarg))
						$numofhours = $numofhoursarg;
					set_transient('zoomd_loglevel',  $newlevel[0], $numofhours * HOUR_IN_SECONDS);
					echo 'Done';
			}
			else
				echo 'Log level argument is not valid (must be a number)';
		}
		else
			echo 'Log level argument is missing';        
	}

   

	/**
	 * loginfo
	 * 
	 * ## EXAMPLES
	 *
	 *     wp zoomd loginfo
	 *
	 * @alias loginfo
	 */
    public function loginfo() { 		
		$this->logger->info("info");
        echo 'Done';
	}
	
}
