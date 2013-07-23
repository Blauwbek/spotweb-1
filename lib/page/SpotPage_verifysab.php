<?php
class SpotPage_verifysab extends SpotPage_Abs {
	private $_params;
	
	public function __construct(Dao_Factory $daoFactory, Services_Settings_Base $settings, array $currentSession) {
		parent::__construct($daoFactory, $settings, $currentSession);
	} # __construct

	function render() {
		# Controleer de users' rechten
		$this->_spotSec->fatalPermCheck(SpotSecurity::spotsec_use_sabapi, '');

		# de output moet niet gecached worden en content is json
		$this->sendExpireHeaders(true);
		$this->sendContentTypeHeader('json');
		
		$nzbHandlerFactory = new Services_NzbHandler_Factory();
		$this->_nzbHandler = $nzbHandlerFactory->build($this->_settings, 
				$this->_currentSession['user']['prefs']['nzbhandling']['action'], 
				$this->_currentSession['user']['prefs']['nzbhandling']);
		
		$resp = $nzbHandlerFactory->verify();
		
		if ($resp['res'] === null) {
			$result = json_encode(array('bc' => '#f99797', 'text' => 'Failure (wrong settings?)'));
		} else if ($resp['res']) {
			$result = json_encode(array('bc' => '#cbffcb', 'text' => 'Succes!'));
		} else if ($resp['res'] === false) {
			$result = json_encode(array('bc' => '#f99797', 'text' => 'Something went wrong: ' . json_decode($resp['data'])['error']));
		} else {
			$result = json_encode(array('bc' => '#f99797', 'text' => 'Something went wrong while verifying, try again.')); #Dit zou nooit moeten gebeuren, maar voor de zekerheid...
		}# if

		echo $result;
	} # render
} # class SpotPage_verifysab
