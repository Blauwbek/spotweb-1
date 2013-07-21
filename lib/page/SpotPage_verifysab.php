<?php
class SpotPage_verifysab extends SpotPage_Abs {

	function __construct(SpotDb $db, SpotSettings $settings, $currentSession, $params) {
		parent::__construct($db, $settings, $currentSession);

		$this->_params = $params;
	} # __construct

	function render() {
		# Controleer de users' rechten
		$this->_spotSec->fatalPermCheck(SpotSecurity::spotsec_use_sabapi, '');

		# de output moet niet gecached worden
		$this->sendExpireHeaders(true);
		
		if (substr($this->_params['saburl'], -1) != '/') {
			$this->_params['saburl'] .= '/';
		} # if
		
		$output = @file_get_contents($this->_params['saburl'] . 'sabnzbd/api?mode=qstatus&apikey=' . $this->_params['sabkey']);
		
		if (empty($output)) {
			$result = json_encode(array('bc' => '#f99797', 'text' => 'Failure (host unreachable?)'));
		} else if (substr(trim($output), 0, 5) != 'error') {
			$result = json_encode(array('bc' => '#cbffcb', 'text' => 'Succes!'));
		} else {
			$result = json_encode(array('bc' => '#f99797', 'text' => 'Something went wrong: '.trim($output)));
		} # if
		
		echo $result;
	} # render
} # class SpotPage_verifysab