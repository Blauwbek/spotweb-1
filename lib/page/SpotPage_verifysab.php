<?php
class SpotPage_verifysab extends SpotPage_Abs {
	private $_params;
	private $httpreq = new Services_Providers_Http(null);
	
	public function __construct(Dao_Factory $daoFactory, Services_Settings_Base $settings, array $currentSession, array $params) {
		parent::__construct($daoFactory, $settings, $currentSession);

		$this->_params = $params;
	} # __construct

	function render() {
		# Controleer de users' rechten
		$this->_spotSec->fatalPermCheck(SpotSecurity::spotsec_use_sabapi, '');

		# de output moet niet gecached worden
		$this->sendExpireHeaders(true);
		$this->sendContentTypeHeader('json');

		if (substr($this->_params['saburl'], -1) != '/') {
			$this->_params['saburl'] .= '/';
		} # if
		
		if (substr($this->_params['saburl'], 0, 4) != 'http') {
			$this->_params['saburl'] = 'http://'.$this->_params['saburl'];
		} # if
		
		if (!empty($this->_params['httphead'])){
			$userpass = explode(':', $this->_params['httphead']);
			$url = $this->_params['saburl'] . "sabnzbd/api?mode=qstatus&ma_username=$userpass[0]&ma_password=$userpass[1]";
		} else {
			$url = $this->_params['saburl'] . 'sabnzbd/api?mode=qstatus&apikey=' . $this->_params['sabkey'];
		} # if
		
		$resp = $httpreq->perform($url, null);
		
		if (!$resp['successful']) {
			$result = json_encode(array('bc' => '#f99797', 'text' => 'Failure (wrong url?)'));
		} else if ($resp['successful'] && substr(trim($resp['data']), 0, 5) != 'error') {
			$result = json_encode(array('bc' => '#cbffcb', 'text' => 'Succes!'));
		} else {
			$result = json_encode(array('bc' => '#f99797', 'text' => 'Something went wrong: ' . trim($resp['data'])));
		} # if

		echo $result;
	} # render
} # class SpotPage_verifysab
