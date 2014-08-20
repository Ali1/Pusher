<?php

App::uses('String', 'Utility');

class PusherHelper extends Helper {
	
	public $helpers = array('Html', 'Js');

	private $appKey = '';

	public $jsInitiated = false;

	function initJs() {
		$this->appKey = Configure::read('Pusher.credentials.appKey');
		$this->Js->buffer("pusher = new Pusher('" . $this->appKey . "', {authEndpoint: '" . $this->url(array('plugin' => 'pusher', 'controller' => 'pusher', 'action' => 'auth.json')) . "'});");
		$this->jsInitiated = true;
	}

	public function afterRender($layout) {
		$this->Html->script('//cdnjs.cloudflare.com/ajax/libs/pusher/2.1.6/pusher.min.js', array('inline' => false));
	}

	public function subscribe($channelName, $type = 'public') {
		if (!$this->jsInitiated) {
			$this->initJs();
		}
		$channelName = strtolower($channelName);
		$channelName = ($type == 'private' || $type == 'presence') ? $type . '-' . $channelName : $channelName;
		$this->Js->buffer('pusher.subscribe(\'' . $channelName . '\')');
	}

	public function bindChannelEvent($channelName, $event, $script) {
		$this->Js->buffer(
			$this->getChannel($channelName) . '.bind("' . $event . '", function(data) {
				' . $script . '
			});'
		);
	}

	public function bindChannel($channelName, $script) {
		$this->Js->buffer(
			$this->getChannel($channelName) . '.bind_all(function(event_name, data) {
				' . $script . '
			});'
		);
	}

	public function bindEvent($event, $script) {
		$this->Js->buffer(
			'pusher.bind("' . $event . '", function(data) {
				' . $script . '
			});'
		);
	}

	public function bindAll($script) {
		$this->Js->buffer(
			'pusher.bind_all(function(event_name, data) {
				' . $script . '
			});'
		);
	}

	private function getChannel($channelName) {
		return 'pusher.channel(\'' . $channelName . '\')';
	}
}

?>