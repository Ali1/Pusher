<?php

class PusherController extends PusherAppController {
	
	public $components = array('RequestHandler', 'Pusher.Pusher');

	public function beforeFilter() {
		parent::beforeFilter();
		if (isset($this->Security)) {
			$this->Security->unlockedActions = ['auth'];
		}
	}
	public function auth() {
		if($this->request->is('post') && isset($this->request->data['channel_name']) && isset($this->request->data['socket_id'])) {
			$authData = '';
			switch($this->Pusher->getChannelType($this->request->data['channel_name'])) {
				case 'private':
					if ($this->_pusherCanAccessPrivateChannel($this->request->data['channel_name'], $this->request->data['socket_id'])) {
						$authData = $this->Pusher->privateAuth(
							$this->request->data['channel_name'],
							$this->request->data['socket_id']
						);
					} else {
						throw new ForbiddenException();
					}
					break;
				case 'presence':
					//todo
					break;
				default:
					throw new MethodNotAllowedException();
					break;
			}
			$this->set('auth', $authData);
		}
		else {
			throw new MethodNotAllowedException();
		}
	}

}

?>