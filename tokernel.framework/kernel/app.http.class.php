<?php
/**
 * toKernel - Universal PHP Framework.
 * Main application class for working with http mode.
 * Child of app_core class.
 * 
 * This file is part of toKernel.
 *
 * toKernel is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * toKernel is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with toKernel. If not, see <http://www.gnu.org/licenses/>.
 *
 * @category   kernel
 * @package    framework
 * @subpackage kernel
 * @author     toKernel development team <framework@tokernel.com>
 * @copyright  Copyright (c) 2016 toKernel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @version    1.5.0
 * @link       http://www.tokernel.com
 * @since      File available since Release 1.0.0
 */

/* Restrict direct access to this file */
defined('TK_EXEC') or die('Restricted area.');

/**
 * app class
 *  
 * @author David A. <tokernel@gmail.com>
 */
class app extends app_core {
	
/**
 * Main output buffer content
 * 
 * @access private
 * @staticvar string
 */
 private static $output_buffer = '';

/**
 * Headers to send
 * 
 * @access private
 * @staticvar array
 */
 private static $headers = array(); 
 
/**
 * Initialized Template file name of main 
 * callable addon without '*.tpl.php' extension. 
 * 
 * @access private
 * @var string
 */ 
 private $template;

/**
 * Template parsing variables
 *
 * @access private
 * @var array
 */
 private $template_vars = array();

/**
 * Is output buffer from cache.
 * This variable will true, if content loaded from cache.
 * 
 * @access private
 * @var bool 
 */
 private $cached_output = false;


/**
 * Global variables to use in templates and view files.
 * Example: {var.base_url} will converted to application base url.
 *
 * @access private
 * @var array
 */
 private $variables = array();

/**
 * Main function for application.
 * This function calling from tokernel.inc.php file at once, and 
 * call the action function of requested addon prefixed by 'action_'. 
 * Second time calling this function from any part of application 
 * will generate error. 
 * 
 * @final
 * @access public
 * @return bool 
 */
 final public function run() {

 	/* Calling this function second time will trigger error. */
 	if(self::$runned) {
	   trigger_error('Application is already runned. '.__CLASS__.'::'.
	                  __FUNCTION__.'()', E_USER_ERROR);
	}
	 
	tk_e::log_debug('Start', 'app->'.__FUNCTION__);

	/* Define hooks object */
	require(TK_PATH . 'kernel/hooks.class.php');
	$this->hooks = new hooks();
	
	tk_e::log_debug('Loaded "hooks" object', 'app->'.__FUNCTION__);
	
	/* Call first hook before main addon call */
	if($this->config->item_get('allow_hooks', 'APPLICATION') == 1) {
		tk_e::log_debug('Running application hooks (before)', 'app->'.__FUNCTION__);
		$this->hooks->before_run();
	}
	
	/* Call second hook before main addon call */
	if($this->config->item_get('allow_http_hooks', 'HTTP') == 1) {
		tk_e::log_debug('Running HTTP hooks (before)', 'app->'.__FUNCTION__);
		$this->hooks->http_before_run();
	}

	/* 
	 * Load content from cache if request method is not POST.
	 * To disable caching, set cache_expiration to 0 
	 * in application configuration file. the value -1 assumes
	 * that the cache will never expire.
	 */
	$ce_ = $this->config->item_get('cache_expiration', 'CACHING');
	
	if(($ce_ > 0 or $ce_ == '-1') and $_SERVER['REQUEST_METHOD'] == 'GET') {
		
		tk_e::log_debug('Trying to load content from cache', 'app->'.__FUNCTION__);
							
		/* 
		 * Get cache content from application cache directory.
		 * Note: Function 'cache_get_content' will return false, 
		 * if cached file exist but cache expired.  
		 */
		$cached_content = $this->lib->cache->get_content($this->lib->url->url(true, true, true), $ce_);
		
        /* 
         * Cached content is not empty.
         * Output content and return true. 
         */
		if($cached_content) {
			
			self::$output_buffer = '';
			
			self::$output_buffer .= $cached_content;
			
			$this->cached_output = true;

			unset($cached_content);
			
			/* Set application run status */
			self::$runned = true;
			
			tk_e::log_debug('Output content from cache', 'app->'.__FUNCTION__);

			/* Output Application buffer */
			$this->output();
			
			/* Call Last hook after main addon call in HTTP mode */
			if($this->config->item_get('allow_http_hooks', 'HTTP') == 1) {
				tk_e::log_debug('Running HTTP hooks (after)', 'app->'.__FUNCTION__);
				$this->hooks->http_after_run();
			}
			
			/* Call Last hook after main addon call */
			if($this->config->item_get('allow_hooks', 'APPLICATION') == 1) {
				tk_e::log_debug('Running application hooks (after)', 
														'app->'.__FUNCTION__);
				$this->hooks->after_run();
			}
			
			tk_e::log_debug('End with cache content', 'app->'.__FUNCTION__);
			
			return true;
			 
		} // end if content
		
		tk_e::log_debug('Cache expired or not exists.',  'app->'.__FUNCTION__); 
		
	} // end checking cache_expiration

	/* Set id_addon for load */
	$id_addon = $this->lib->url->addon();

	/* Check, if the default calable addon not exists */
	$addon_exists = $this->addons->exist($id_addon);

	if($addon_exists == false and $id_addon == $this->config->item_get('default_callable_addon', 'HTTP')) {
		
		tk_e::log_debug('End. Default callable addon not exists.', 
									'app->'.__FUNCTION__);
			
		tk_e::error(E_USER_ERROR, 'Default callable addon not exists.', 
									__FILE__, __LINE__);
	}
	
	/* Check, if the callable addon not exists */
	if($addon_exists == false) {
		$this->error_404('Addon `'.$id_addon.'` not exists.', __FILE__, __LINE__);
	}

	/* Load main callable addon object */
	$addon = $this->addons->load($id_addon);
	
    /* Check, is addon is object */ 
    if(!is_object($addon)) {
    	
    	tk_e::log('`'.$id_addon.'` - $addon is not an object returned ' . 
    				'by $this->addons->load() !', E_USER_ERROR,
    				__FILE__, __LINE__);
    				
    	$this->error_404('Addon `'.$id_addon.'` is not an object.', 
    						__FILE__, __LINE__);
    }
	
	/* Check, is addon allowed under current run mode */
	if($addon->config('allow_http', 'CORE') != '1') {
		trigger_error('Addon "'.$id_addon.'" cannot run under HTTP mode!', 
						E_USER_ERROR);
		return false;
	}
	
	/* Set action to call */
	$action = $this->lib->url->action();
	
	/* Check, if action with 'ax_' ajax prefix */
	if($action != '' and substr($action, 0, 3) == 'ax_') {
		
		tk_e::log_debug('Error: Action "' . $action . 
						'" with "ax_" prefix not allowed!', 
						'app->'.__FUNCTION__);
		
		$this->error_404('Action "'.$action.'" with "ax_" prefix not allowed!');
	}

	/* Define callable method of addon */
 	if($this->is_ajax_request() == true) {
		$function_to_call = 'action_ax_'.$action;
	} else {
		$function_to_call = 'action_'.$action;
	}
	
    /* Check, is method of addon exists */
    if(!method_exists($addon, $function_to_call)) {
    
 		$this->error_404('Method `'.$function_to_call.'()` in addon ' .
    					 'class `'.$id_addon.'` not exists.', 
    					 __FILE__, __LINE__);

	}
    	
	tk_e::log_debug('Call addon\'s action - "' . 
					$addon->id() . "->" . $function_to_call . '"', 
					'app->'.__FUNCTION__);

	/* Set application global variables to use in templates and view files */
	$this->load_default_vars();

	/* Get buffered result of addon's action. */
	ob_start();
	
	//call_user_func_array(array($addon, $function_to_call), $this->params());
    $addon->$function_to_call($this->lib->url->params());
	
	$addon_called_func_buffer = ob_get_contents();
	ob_end_clean();
    unset($function_to_call);
	
    /* 
     * Set main callable template as action if it is 
     * not defined in addon method and in aliasing file.
     * 
     * Ex: $this->app->set_template();
     */
    
    if($this->template == '') {
    	$this->template = $id_addon.'.'.$action;
    }
    
    /* 
     * Load template object instance
     */
	$template_obj = $this->lib->template->instance($this->template, $this->template_vars);

	/* 
	 * Interpret template if template buffer is not empty.
	 * Addon can have actions such as ajax requests which can not have template
	 * files, because templates mines the full html document.
	 * So Ajax request action can use only 'view' files.
	 */
	self::$output_buffer = '';
	
	if($template_obj) {
		self::$output_buffer .= $template_obj->run($addon_called_func_buffer);
	} else {
		self::$output_buffer = $addon_called_func_buffer;
		
		tk_e::log_debug('Template file "'. $this->template . '" not detected',
						'app->'.__FUNCTION__);
	}

	/* 
	 * Try to write content to cache.
	 */
	if(($ce_ > 0 or $ce_ == '-1') and $_SERVER['REQUEST_METHOD'] == 'GET') {

		tk_e::log_debug('Trying to write content to chache.', 
							'app->'.__FUNCTION__);
		
		$to_cache_content = self::$output_buffer;
		
		$this->lib->cache->write_content($this->lib->url->url(true, true, true), 
											$to_cache_content, $ce_);
											
	} // end checking cache_expiration
	
	/* Output Application buffer */
	$this->output();
	
	/* Call last hook after main addon call in HTTP mode */
	if($this->config->item_get('allow_http_hooks', 'HTTP') == 1) {
		tk_e::log_debug('Running HTTP hooks (after)', 'app->'.__FUNCTION__);
		$this->hooks->http_after_run();
	}
	
	/* Call last hook after main addon call */
	if($this->config->item_get('allow_hooks', 'APPLICATION') == 1) {
		tk_e::log_debug('Running application hooks (after)', 'app->'.__FUNCTION__);
		$this->hooks->after_run();
	}

    self::$runned = true;
    
    tk_e::log_debug('End', 'app->'.__FUNCTION__);
    
	return true;

} // end func run

/**
 * Output application buffered content.
 * 
 * @access protected
 * @return void
 */ 
 protected function output() {
 	
 	/* Call second hook before output buffer */
 	if($this->config->item_get('allow_http_hooks', 'HTTP') == 1) {
		tk_e::log_debug('Running hooks before output', 'app->'.__FUNCTION__);
 		$this->hooks->http_before_output();
	}
	
	/* Send headers */
	foreach(self::$headers as $header) {
 		@header($header[0], $header[1]);
 	}
 	
 	/* Output buffer and clean */
	echo self::$output_buffer;
		
 	if(!TK_GZIP_OUTPUT) {
		tk_e::log_debug('Output not compressed content', 'app->'.__FUNCTION__);
	} else {
		tk_e::log_debug('Output compressed content', 'app->'.__FUNCTION__);
	}
	
	self::$headers = array();
	self::$output_buffer = '';
	
	/* Call hook after output buffer */
	if($this->config->item_get('allow_http_hooks', 'HTTP') == 1) {
		tk_e::log_debug('Running hooks after output', 'app->'.__FUNCTION__);
		$this->hooks->http_after_output();
	}
	
 } // end func output

/**
 * Redirect
 * 
 * @access public
 * @param string $url
 * @return void
 */ 
 public function redirect($url) {
 	header('Location: '.$url);
 	exit();
 } // end func redirect

/**
 * Set application global variable
 *
 * @access public
 * @param string $item
 * @param mixed $value
 * @return void
 * @since 1.5.0
 */
 public function set_var($item, $value) {
	 $this->variables[$item] = $value;
 } // End func set_var

/**
 * Get application global variable by name
 *
 * @access public
 * @param string $item
 * @return mixed
 * @since 1.5.0
 */
 public function get_var($item) {

	 if(!isset($this->variables[$item])) {
		 return false;
	 }

	 return $this->variables[$item];

 } // End func get_var

/**
 * Get application all global variables
 *
 * @access public
 * @return mixed
 * @since 1.5.0
 */
 public function get_vars() {
	return $this->variables;
 } // End func set_var

/**
 * Set header to output
 * 
 * @access public
 * @param string $header
 * @param bool $replace
 * @return void
 */ 
 public function set_header($header, $replace = true) {
	self::$headers[] = array($header, $replace);
 }
 
/**
 * Show error 404
 * 
 * @access public
 * @param string $message
 * @param string $file
 * @param integer $line
 */
 public function error_404($message = NULL, $file = NULL, $line = NULL) {
 	
 	if(is_null($message)) {
 		$message = 'Called app->error_404()';
 	}
 	
 	if(is_null($file)) {
 		$file = __FILE__;
 	}
 	
 	if(is_null($line)) {
 		$line = __LINE__;
 	}

	$remote_address = $this->lib->filter->server('REMOTE_ADDR');

	if($this->config->item_get('log_errors_404', 'ERROR_HANDLING') == 1) {
		$log_ext = $this->config->item_get('log_file_extension', 'ERROR_HANDLING');
		$log_obj = $this->lib->log->instance('error_404.' . $log_ext);
		$log_obj->write($message . '  CLIENT IP: ' .$remote_address . ' | URL: ' . $this->lib->url->query_string() . ' | FILE: ' . $file . ' | LINE: ' . $line);
	}

	if($this->is_ajax_request()) {

		tk_e::log_debug($message . ' CLIENT IP: ' .$remote_address . ' | URL: "'. $this->lib->url->query_string() . '"',	'app->'.__FUNCTION__);
		tk_e::log_debug('', ':============= END WITH ERROR 404 ==============');

		header('HTTP/1.0 404 Not Found');
		echo $this->language('err_404_subject');

		exit();
	}

	/* Set application global variables to use in templates and view files */
	$this->load_default_vars();

	$this->variables['title'] = $this->language('err_404_subject');

	if($this->config->item_get('app_mode', 'RUN_MODE') == 'development') {
		$this->variables['message'] = $message;
	} else {
		$this->variables['message'] = $this->language('err_404_message');
	}

	$this->set_template('error_404', $this->variables);
	$this->set_header('HTTP/1.0 404 Not Found', true);

	/*
	 * Load template object instance
	 */
	$template_obj = $this->lib->template->instance($this->template, $this->template_vars);

	self::$output_buffer = '';

	if($template_obj) {
		 self::$output_buffer .= $template_obj->run();
	} else {
		 self::$output_buffer = $this->language('err_404_subject');

		 tk_e::log_debug('Template file "'. $this->template . '" not detected',	'app->'.__FUNCTION__);
	}

	/* Output Application buffer */
	$this->output();

	self::$runned = true;

	tk_e::log_debug($message . ' CLIENT IP: ' .$remote_address . ' | URL: "'. $this->lib->url->query_string() . '"',	'app->'.__FUNCTION__);
	tk_e::log_debug('', ':============= END WITH ERROR 404 ==============');

	exit();

 } // end func error_404
  
/**
 * Return, is output from cache
 * 
 * @access public
 * @return bool
 */ 
 public function cached_output() {
	return $this->cached_output;
 }

/**
 * Return request method
 * 
 * @access public
 * @return bool
 */
 public function request_method() {
	
	if(isset($_SERVER['REQUEST_METHOD'])) {
		return $_SERVER['REQUEST_METHOD'];
	}

	return false;
	
 } // end func request_method 

/**
 * Is Ajax request
 * 
 * @access public
 * @return bool
 */
 public function is_ajax_request() {
	
 	return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
					$_SERVER['HTTP_X_REQUESTED_WITH']=="XMLHttpRequest");
 } 
 
/**
 * Return template of main addon.
 * 
 * @access public
 * @return string
 */
 public function get_template() {
	
	if(!isset(self::$instance)) {
 		trigger_error('Application instance is empty ('.__CLASS__.')', 
	              E_USER_ERROR );
 	}
	
	return $this->template;

 } // end func template

/**
 * Set template for application
 * 
 * @access public
 * @param string $tempalte
 * @return bool
 */ 
 public function set_template($template, $template_vars = NULL) {
	
	if(!isset(self::$instance)) {
 		trigger_error('Application instance is empty ('.__CLASS__.')', 
	              E_USER_ERROR );
 	}

	$this->template = $template;
	$this->template_vars = $template_vars;

 } // end func set_template
  
/**
 * Return language value by item
 * return language prefix, if item is null
 * 
 * @access public
 * @param string $item
 * @return string
 */ 
 public function language($item = NULL) {
 	
 	if(!isset(self::$instance)) {
 		trigger_error('Application instance is empty ('.__CLASS__.')', 
	              E_USER_ERROR );
 	}
 	
 	if(is_null($item)) {
 		return $this->lib->url->language_prefix();
 	}
 	
 	if(func_num_args() > 1) {
 		$l_args = func_get_args();
 	
 		unset($l_args[0]);
 		
 		if(is_array($l_args[1])) {
 			$l_args = $l_args[1];
 		}
 		
 		return $this->language->get($item, $l_args);
 	}
 	
 	return $this->language->get($item);
 	
 } // end func language

/**
 * Return Application allowed languages for HTTP mode
 * 
 * @access public
 * @param string $lp
 * @return array
 */
 public function allowed_languages($lp = NULL) {
 	
 	$a_languages = explode('|' , 
 							$this->config->item_get('http_allowed_languages', 
 													'HTTP'));
 	if(!is_array($a_languages)) {
 		return false;
 	}
 	
 	$tk_lng_ref_file = TK_PATH . 'config' . TK_DS . 'languages.ini';
 	$app_lng_ref_file = TK_APP_PATH . 'config' . TK_DS . 'languages.ini';
 	
 	if(is_file($app_lng_ref_file)) {
 		$lng_ref = $this->lib->ini->instance($app_lng_ref_file);
 	} else {
 		$lng_ref = $this->lib->ini->instance($tk_lng_ref_file);
 	}
 	
 	if(!is_null($lp)) {
 		$language = array($lp => $lng_ref->item_get($lp));
 		unset($a_languages);
 		unset($lng_ref);
 		return $language;
 	}
 	
 	foreach($a_languages as $lng) {
 		$languages[$lng] = $lng_ref->item_get($lng);
 	}
 	
 	unset($lng_ref);
 	unset($a_languages);
 	return $languages;
 	
 } // end func allowed_languages
 
/**
 * Set application language by prefix
 * 
 * @access public
 * @param string $lp
 * @return void
 */ 
 public function set_language($lp) {
 	
 	if(!isset(self::$instance)) {
 		trigger_error('Application instance is empty ('.__CLASS__.')', 
	              E_USER_ERROR );
 	}
 	
 	/* Check is language prefix enabled*/
 	if(!in_array($lp, explode('|', $this->config->item_get('http_allowed_languages', 'HTTP')))) {
			
		$lp = $this->config->item_get('http_default_language', 'HTTP'); 
	}
 	
 	$this->lib->url->set_language_prefix($lp);
 	
 	/* Load language object for application */
	self::$instance->language = self::$instance->lib->language->instance(
    					$lp,
     					array(
					        TK_APP_PATH . 'languages/',
     						TK_PATH . 'languages/', 
     					),
     					true);

 } // end func set_language

/**
 * Set application mode frontend | backend
 * 
 * @access public
 * @param string $mode frontend | backend
 * @return void
 */ 
 public function set_mode($mode) {
	$this->lib->url->set_mode($mode);
 } // end fnc set_mode
 
/**
 * Return application mode frontend | backend
 * 
 * @access public
 * @return string
 */ 
 public function get_mode() {
 	return $this->lib->url->mode();
 }
 
/**
 * Return application theme name by run mode.
 * 
 * @access public
 * @param mixed
 * @return string
 */
 public function theme_name($mode = NULL) {
	 
	if(is_null($mode)) {
		$mode = $this->get_mode();
	} 
	 
 	return $this->config->item_get($mode.'.theme', 'HTTP');
 } // end func theme

/**
 * Return application theme path.
 * 
 * @access public
 * @param mixed
 * @return string
 */
 public function theme_path($mode = NULL) {
	 
	if(is_null($mode)) {
	 	$mode = $this->get_mode();
	}
	 
 	return TK_APP_PATH . 'themes' . TK_DS . $mode . TK_DS .
 			$this->config->item_get($mode.'.theme', 'HTTP') . TK_DS;

 } // end func theme
 
/**
 * Return theme url
 * 
 * @access public
 * @param mixed
 * @return string
 */ 
 public function theme_url($mode = NULL) {
 	
	 if(is_null($mode)) {
	 	 $mode = $this->get_mode();
	 }
	 
	 $url = '';
	 
	 if(TK_APP_DIR != '') {
		$url = $this->lib->url->base_url() . '/' . TK_APP_DIR . '/themes/' .
				$mode . '/' .
				$this->config->item_get($mode . '.theme', 'HTTP');
	 } else {
		$url = $this->lib->url->base_url() . '/themes/' . $mode . '/' .
				$this->config->item_get($mode . '.theme', 'HTTP');
	 }	
	 
	 return $url;
 	
 } // end func theme_url
 
/**
 * Return url of application directory
 * 
 * @access public
 * @return string
 */
 public function app_url() {
 	
	 $url = '';
	 
	 if(TK_APP_DIR != '') {
		$url = $this->lib->url->base_url() . '/' . TK_APP_DIR;
	 } else {
		$url = $this->lib->url->base_url();
	 }
	 
	 return $url;
	 
 } // end func custom_url
  
/**
 * Return url of framework directory path
 * 
 * @access public
 * @return string
 */
 public function tk_url() {
 	return $this->lib->url->base_url() . TK_DIR;
 } // end func custom_url

/**
 * Load Default variables
 *
 * @access protected
 * @return void
 */
 protected function load_default_vars() {

	 /* Set application global variables to use in templates and view files */
	 $this->variables['base_url'] = $this->lib->url->base_url();
	 $this->variables['base_url_lng'] = $this->lib->url->base_url() . '/' . $this->language();
	 $this->variables['app_url'] = $this->app_url();
	 $this->variables['theme_name'] = $this->theme_name();
	 $this->variables['theme_url'] = $this->theme_url();
	 $this->variables['theme_images_url'] = $this->theme_url() . '/images';
	 $this->variables['theme_js_url'] = $this->theme_url() . '/js';
	 $this->variables['theme_css_url'] = $this->theme_url() . '/css';
	 $this->variables['uploads_url'] = $this->lib->url->base_url() . '/uploads';
	 $this->variables['language_prefix'] = $this->language();
	 $this->variables['date'] = date('d/m/Y');
	 $this->variables['day'] = date('d');
	 $this->variables['month'] = date('m');
	 $this->variables['year'] = date('Y');

 } // End func load_default_vars

/* End of class app */
}

/* End of file */
?>