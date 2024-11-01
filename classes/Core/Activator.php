<?php
namespace Zoomd\Core;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

use Zoomd\Core\Settings;
use Zoomd\Core\Logger;
use Zoomd\Core\HttpClient;
use zoomd_indexer;
use Zoomd\Core\PostUpdater;


class Activator {

    protected $logger;
	protected $httpClient;
    
    public function __construct(){
        $this->logger = new Logger();
		$this->httpClient = new HttpClient();
    }

    public function activate() { 
        $siteurl = Settings::siteUrl();
        $settingsBaseUrl = get_admin_url() . 'admin.php?page=zoomd';
        $settingsUrl = "<a class='zoomd-goto-setting-link' href='" . $settingsBaseUrl . "'>Zoomd Settings</a>";
        
        update_option('zoomd_notices', "<h1 class='zoomd-h1'>Zoomd Search is ready, Please go to " . $settingsUrl . " and register your account</h1>" );
        $this->logger->Warn("activate called successfully."); 
    }
    
	public function start() { 		
		if(Settings::isValid()) {
			
			 self::registerEvents();
			 update_option('zoomd_notices', "Zoomd Search is ready" );
			 $this->logger->Warn("register called successfully.");             
		}
		else{
			self::unRegisterEvents();
			$this->logger->Warn("registration failed. UnRegister Events called.");
		}
	}

	public function deactivate(){
        $this->logger->Warn("deactivate called");
		$this->deactivatesite();
        
        wp_clear_scheduled_hook( 'zoomd_action' );   		
        // if(shortcode_exists(TSShortCodeTag))
        //     remove_shortcode(TSShortCodeTag);

		
	}

	public function deactivatesite()
	{		
		try
		{
			$tok = Settings::siteId() . ':' . Settings::apiHash();
			$siteurl = Settings::siteUrl();
			$siteId = Settings::siteId();
			$clientId = Settings::clientId();	
            $email = Settings::email();		
			//$this->logger->Warn("deactivating site". $siteId . ' URL: ' . $siteurl);
			$lang = $this->getLocaleCodeForDisplayLanguage(substr(get_locale(),0,2));

			$regdata = '{"url": "%1$s", "siteid":"%2$s", "clientid":"%3$s","language":"%4$s", "email":"%5$s"}';      
			$regdata = sprintf($regdata, $siteurl,$siteId,$clientId,$lang,$email);
			
			$this->logger->Warn('Deactivating site: ' . $regdata);
			//prepare headers
			$headers = array(
				'Content-Type: application/json',
				'Referer: ' . $siteurl,
				'Content-Length: ' . strlen($regdata),
				'RequestVerificationToken: ' . str_replace('"', "", $tok));

			$httpResponse =$this->httpClient->post(DeactivateEndpointVal,$regdata,$headers);
		}
		catch (Exception $e) 
		{
			$this->logger->Error("Activator::DeactivateSite Failed . jsonUrl =" .$egetMessage());			
		}
	}

	public function uninstall()
	{			
		try
		{
			$tok = Settings::siteId() . ':' . Settings::apiHash();						
			$siteurl = Settings::siteUrl();
			$siteId = Settings::siteId();
			$clientId = Settings::clientId();	
			$email = Settings::email();		
			$this->logger->Warn("uninstalling site: " . $siteId . ' URL: ' . $siteurl);
            $lang = $this->getLocaleCodeForDisplayLanguage(substr(get_locale(),0,2));
			
			$regdata = '{"url": "%1$s", "siteid":"%2$s", "clientid":"%3$s","language":"%4$s", "email":"%5$s"}';      
			$regdata = sprintf($regdata, $siteurl,$siteId,$clientId,$lang,$email);

			//prepare headers
			$headers = array(
				'Content-Type: application/json',
				'Referer: ' . $siteurl,
				'Content-Length: ' . strlen($regdata),
				'RequestVerificationToken: ' . str_replace('"', "", $tok));

			$httpResponse =$this->httpClient->post(UnRegisterEndpointVal,$regdata,$headers);

			Settings::deleteAllOptions();
		}
		catch (Exception $e) 
		{
			$this->logger->Error("Activator::UnregisterSite Failed . jsonUrl =" .$egetMessage());			
		}	
	}

	public static function registerEvents(){
		
        add_action('zoomd_action',array('Zoomd\Core\Activator', 'zoomd_action_index' ));	
		
		wp_clear_scheduled_hook( 'zoomd_action' ); 
		wp_schedule_event( time(), 'hourly', 'zoomd_action');

		//Start the first scan
		//do_action('zoomd_action');
		
	}

	public static function unRegisterEvents(){
		remove_action( 'save_post', array('Zoomd\Core\Activator','post_updated'), 10, 3 );
		remove_action('zoomd_action',array('Zoomd\Core\Activator', 'zoomd_action_index' ));
		remove_filter( "the_content", array('ContentUpdater' , 'custom_content_after_post' ));

		//remove
		wp_clear_scheduled_hook( 'zoomd_action' ); 
		wp_schedule_event( time(), 'hourly', 'zoomd_action');
	}

	public static function zoomd_action_index(){
       
	   $indexer = new zoomd_indexer();
       $indexer->index();
    }

	public static function post_updated($post_id, $post, $update){
		PostUpdater::post_updated($post_id, $post, $update);
	}
	/*
		Provision
	*/
    public function validate($clientid,$apikey){    
		
        $this->logger->Info("Activator::validation started");
		$siteurl = Settings::siteUrl();		
		$jsonurl = ValidationEndpoint;    
        $email = Settings::email();    
		$lang = $this->getLocaleCodeForDisplayLanguage(substr(get_locale(),0,2));
		$siteinfo = sprintf('URL: %1$s, Email: %2$s',$siteurl,$email);
        
        //validation API
        $regdata = '{"siteUrl": "%1$s", "clientId":"%2$s"}';      
        $regdata = sprintf($regdata,$siteurl, $clientid);

		//prepare headers
		$headers = array(
            'Content-Type: application/json',
			'Referer: ' . $siteurl,
            'Content-Length: ' . strlen($regdata),
            'RequestVerificationToken: ' . str_replace('"', "", $apikey));

        $httpResponse =$this->httpClient->post($jsonurl,$regdata,$headers);
		$data = $httpResponse["data"];
		$http_code = $httpResponse["httpCode"];

        
        $this->logger->Info("Validation response :"  .$http_code ."  jsonRes:"  . json_encode($data));
		$success = $data['success']; 
		if(!empty($success) && $success=="true")
		{		
			//Updating Options		
			//$email = wp_get_current_user()->user_email;
            $email = Settings::email();
			Settings::updateOptions($data,$email,$clientid,$apikey);
			Settings::printOptions($this->logger);
			$this->logger->Info("Activator::Validation Called = > OK! SiteId: " .$data['siteId'] .' apiHash: ' .$data['hash']);
			
			return  $data['siteId'];       
		} 
		else
		{
			$this->logger->Error('Validation failed - ' . $siteinfo . ', Request: ' . $regdata . ', Headers: ' . json_encode($headers) . ', response: ' . json_encode($data));
			if(!empty($data["message"]))
				update_option('zoomd_last_error', $data["message"]);			
			return;
		}
	}

	public function getLocaleCodeForDisplayLanguage($name){            
            $languageCodes = array(
            "aa" => "Afar",
            "ab" => "Abkhazian",
            "ae" => "Avestan",
            "af" => "Afrikaans",
            "ak" => "Akan",
            "am" => "Amharic",
            "an" => "Aragonese",
            "ar" => "Arabic",
            "as" => "Assamese",
            "av" => "Avaric",
            "ay" => "Aymara",
            "az" => "Azerbaijani",
            "ba" => "Bashkir",
            "be" => "Belarusian",
            "bg" => "Bulgarian",
            "bh" => "Bihari",
            "bi" => "Bislama",
            "bm" => "Bambara",
            "bn" => "Bengali",
            "bo" => "Tibetan",
            "br" => "Breton",
            "bs" => "Bosnian",
            "ca" => "Catalan",
            "ce" => "Chechen",
            "ch" => "Chamorro",
            "co" => "Corsican",
            "cr" => "Cree",
            "cs" => "Czech",
            "cu" => "Church Slavic",
            "cv" => "Chuvash",
            "cy" => "Welsh",
            "da" => "Danish",
            "de" => "German",
            "dv" => "Divehi",
            "dz" => "Dzongkha",
            "ee" => "Ewe",
            "el" => "Greek",
            "en" => "English",
            "eo" => "Esperanto",
            "es" => "Spanish",
            "et" => "Estonian",
            "eu" => "Basque",
            "fa" => "Persian",
            "ff" => "Fulah",
            "fi" => "Finnish",
            "fj" => "Fijian",
            "fo" => "Faroese",
            "fr" => "French",
            "fy" => "Western Frisian",
            "ga" => "Irish",
            "gd" => "Scottish Gaelic",
            "gl" => "Galician",
            "gn" => "Guarani",
            "gu" => "Gujarati",
            "gv" => "Manx",
            "ha" => "Hausa",
            "he" => "Hebrew",
            "hi" => "Hindi",
            "ho" => "Hiri Motu",
            "hr" => "Croatian",
            "ht" => "Haitian",
            "hu" => "Hungarian",
            "hy" => "Armenian",
            "hz" => "Herero",
            "ia" => "Interlingua (International Auxiliary Language Association)",
            "id" => "Indonesian",
            "ie" => "Interlingue",
            "ig" => "Igbo",
            "ii" => "Sichuan Yi",
            "ik" => "Inupiaq",
            "io" => "Ido",
            "is" => "Icelandic",
            "it" => "Italian",
            "iu" => "Inuktitut",
            "ja" => "Japanese",
            "jv" => "Javanese",
            "ka" => "Georgian",
            "kg" => "Kongo",
            "ki" => "Kikuyu",
            "kj" => "Kwanyama",
            "kk" => "Kazakh",
            "kl" => "Kalaallisut",
            "km" => "Khmer",
            "kn" => "Kannada",
            "ko" => "Korean",
            "kr" => "Kanuri",
            "ks" => "Kashmiri",
            "ku" => "Kurdish",
            "kv" => "Komi",
            "kw" => "Cornish",
            "ky" => "Kirghiz",
            "la" => "Latin",
            "lb" => "Luxembourgish",
            "lg" => "Ganda",
            "li" => "Limburgish",
            "ln" => "Lingala",
            "lo" => "Lao",
            "lt" => "Lithuanian",
            "lu" => "Luba-Katanga",
            "lv" => "Latvian",
            "mg" => "Malagasy",
            "mh" => "Marshallese",
            "mi" => "Maori",
            "mk" => "Macedonian",
            "ml" => "Malayalam",
            "mn" => "Mongolian",
            "mr" => "Marathi",
            "ms" => "Malay",
            "mt" => "Maltese",
            "my" => "Burmese",
            "na" => "Nauru",
            "nb" => "Norwegian Bokmal",
            "nd" => "North Ndebele",
            "ne" => "Nepali",
            "ng" => "Ndonga",
            "nl" => "Dutch",
            "nn" => "Norwegian Nynorsk",
            "no" => "Norwegian",
            "nr" => "South Ndebele",
            "nv" => "Navajo",
            "ny" => "Chichewa",
            "oc" => "Occitan",
            "oj" => "Ojibwa",
            "om" => "Oromo",
            "or" => "Oriya",
            "os" => "Ossetian",
            "pa" => "Panjabi",
            "pi" => "Pali",
            "pl" => "Polish",
            "ps" => "Pashto",
            "pt" => "Portuguese",
            "qu" => "Quechua",
            "rm" => "Raeto-Romance",
            "rn" => "Kirundi",
            "ro" => "Romanian",
            "ru" => "Russian",
            "rw" => "Kinyarwanda",
            "sa" => "Sanskrit",
            "sc" => "Sardinian",
            "sd" => "Sindhi",
            "se" => "Northern Sami",
            "sg" => "Sango",
            "si" => "Sinhala",
            "sk" => "Slovak",
            "sl" => "Slovenian",
            "sm" => "Samoan",
            "sn" => "Shona",
            "so" => "Somali",
            "sq" => "Albanian",
            "sr" => "Serbian",
            "ss" => "Swati",
            "st" => "Southern Sotho",
            "su" => "Sundanese",
            "sv" => "Swedish",
            "sw" => "Swahili",
            "ta" => "Tamil",
            "te" => "Telugu",
            "tg" => "Tajik",
            "th" => "Thai",
            "ti" => "Tigrinya",
            "tk" => "Turkmen",
            "tl" => "Tagalog",
            "tn" => "Tswana",
            "to" => "Tonga",
            "tr" => "Turkish",
            "ts" => "Tsonga",
            "tt" => "Tatar",
            "tw" => "Twi",
            "ty" => "Tahitian",
            "ug" => "Uighur",
            "uk" => "Ukrainian",
            "ur" => "Urdu",
            "uz" => "Uzbek",
            "ve" => "Venda",
            "vi" => "Vietnamese",
            "vo" => "Volapuk",
            "wa" => "Walloon",
            "wo" => "Wolof",
            "xh" => "Xhosa",
            "yi" => "Yiddish",
            "yo" => "Yoruba",
            "za" => "Zhuang",
            "zh" => "Chinese",
            "zu" => "Zulu"
            );

            if (array_key_exists($name, $languageCodes)) {
                return $languageCodes[$name];
            }       
            else
            {
                return 'English';
            }
        }
}
//it only works this way
add_action('save_post', array('Zoomd\Core\Activator','post_updated'), 10, 3 );		
add_action('zoomd_action',array('Zoomd\Core\Activator', 'zoomd_action_index' ));		



?>