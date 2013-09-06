<?php
class SpotPage_verifynzbh extends SpotPage_Abs {
	private $_params;
	
	public function __construct(Dao_Factory $daoFactory, Services_Settings_Base $settings, array $currentSession) {
		parent::__construct($daoFactory, $settings, $currentSession);
	} # __construct

	function render() {
		$nzbHandlerFactory = new Services_NzbHandler_Factory();
		$this->_nzbHandler = $nzbHandlerFactory->build($this->_settings, 
				$this->_currentSession['user']['prefs']['nzbhandling']['action'], 
				$this->_currentSession['user']['prefs']['nzbhandling']);
		
		$result = json_encode(array('text' => 'No (usable) data.'));
		
		switch ($action)
		{
			case 'save' : {
				$this->_spotSec->fatalPermCheck(SpotSecurity::spotsec_download_integration, 'save');
				if ($this->_nzbHandler->verify())
					$result = json_encode(array('bc' => '#cbffcb', 'text' => 'Succes!'));
				else
					$result = json_encode(array('bc' => '#f99797', 'text' => 'Unable to write to specified folder.'));
				
				break;
			}
			case 'push-sabnzbd' : {
				# Controleer de users' rechten
				$this->_spotSec->fatalPermCheck(SpotSecurity::spotsec_use_sabapi, '');
				
				$resp = $this->_nzbHandler->verify();
				
				if ($resp['res'] === null) {
					$result = json_encode(array('bc' => '#f99797', 'text' => 'Failure (wrong settings?)'));
				} else if ($resp['res']) {
					$result = json_encode(array('bc' => '#cbffcb', 'text' => 'Succes!'));
				} else if ($resp['res'] === false) {
					$result = json_encode(array('bc' => '#f99797', 'text' => 'Something went wrong: ' . json_decode($resp['data'])['error']));
				} else {
					$result = json_encode(array('bc' => '#f99797', 'text' => 'Something went wrong while verifying, try again.')); #Dit zou nooit moeten gebeuren, maar voor de zekerheid...
				}# if
				
				break;
			case 'nzbget' : { 
				$this->_spotSec->fatalPermCheck(SpotSecurity::spotsec_download_integration, 'nzbget');
				$resp = $this->_nzbHandler->verify();
				
				if ($resp['res'])
					$result = json_encode(array('bc' => '#cbffcb', 'text' => 'Succes!'));
				else
					$result = json_encode(array('bc' => '#f99797', 'text' => 'Something went wrong: ' . $resp['data']));
				
				break;
			default : $result = json_encode(array('text' => 'Handler not supported.')); break;
		} # switch
		
		
		# de output moet niet gecached worden en content is json
		$this->sendExpireHeaders(true);
		$this->sendContentTypeHeader('json');

		echo $result;
	} # render
} # class SpotPage_verifysab
