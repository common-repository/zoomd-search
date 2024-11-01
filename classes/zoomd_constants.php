<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
define('BASE_URL', get_bloginfo('url'));

const baseURL = 'http://zadmin.zoomd.com';

const widgetbaseURL = 'zsearch.zoomd.com';

define('PostApiEndpointVal', baseURL . '/api/Wordpress/Upload');
define('GetApiEndpointVal', baseURL . '/api/Wordpress');
define('ValidateEndpointVal', baseURL . '/api/Wordpress/Validate');
define('UnRegisterEndpointVal', baseURL . '/api/Wordpress/UnRegister');
define('DeactivateEndpointVal', baseURL . '/api/Wordpress/Deactivate');
define('RegistrationEndpointVal', baseURL . '/SelfService/Wordpress?url=');


const LASTINDEXED =  'zoomd_last_index_time';
const PostApiEndpoint = PostApiEndpointVal;
const GetApiEndpoint = GetApiEndpointVal;
const ValidationEndpoint = ValidateEndpointVal;
const RegistrationEndPoint = RegistrationEndpointVal;

const loggerUrl = 'https://api.coralogix.com/api/v1/logs';


const MaxBatchSize = 15;

?>