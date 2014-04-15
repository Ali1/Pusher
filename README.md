# A better Pusher plugin for CakePHP 2.x

Massive credits to PCreations which this was forked from however it doesn't seem maintained. This app features better and easier to use security features for private channels and updated Pusher libraries.

This plugin provides a simple access to [Pusher API] (http://pusher.com/) via [the generic PHP Pusher library] (https://github.com/pusher/pusher-php-server).
This plugin also provides a way to generate all the javascript stuff with the Helper

Installation
------------

Copy to your application plugins folder and load it in your bootstrap file :

	CakePlugin::load('Pusher', array('
		'bootstrap' => true
	'));

Add the PusherBehavior and the PusherHelper in your behaviors/helpers list :
	
	//YourModel.php
	public $actsAs = array('Pusher.Pusher');

	//YourController.php
	public $helpers = array('Pusher.Pusher');

Your application needs to be register on [Pusher website] (http://pusher.com/). You need to add the following lines to your Config/bootstrap.php file:

	Configure::write(array(
		'Pusher' => array(
			'credentials' => array(
				'appKey' => 'YOUR_KEY',
				'appSecret' => 'YOUR_APP_SECRET',
				'appId' => 'YOUR_APP_ID'
			)
		)
	));

How to use it
-------------

### Trigger Event

Trigger an event on a channel is very simple. It's a server-side flow. In your controller just do the following :

	//Some event information
	$data = array('message' => 'Something happened');

	//Trigger an event named EVENT_NAME on the CHANNEL_NAME channel. You can use private and presence channel by prefixing the name by private- or presence-. See pusher docs (http://pusher.com/docs/client_api_guide/client_channels) for details
	$this->YourModel->trigger(CHANNEL_NAME, EVENT_NAME, $data);

### Subscribe to a channel

"Receiving" a pushed event is a client-side flow, enjoy the realtime functionnality using Pusher Helper for generating javascript stuff :

	$this->Pusher->subscribe(CHANNEL_NAME);
	//The third argument receive string will be parsed as javascript.
	$this->Pusher->bindEvent(CHANNEL_NAME, EVENT_NAME, "console.log('An event was triggered with message '+data.message+');");

### Authentication with private channels

When using private channel, authentication is handled by the Pusher Controller. However you must provide a method in AppController to verify the request to access a private channel. This channel must return a boolean (true or false).

	//Controller/AppController.php
	public function _pusherCanAccessPrivateChannel($channelName, $socketId) {
		// handle user channels e.g. private-user-4324
		if (preg_match('/private-user-([0-9]+)/i', channelName, $matches)) {
			if ($matches[1] == $this->Auth->user('id')) {
				return true;
			} else {
				return false;
			}
		}
		return false; // unrecognised channel name
	}

Example
-------

A very simple example could be this :

	//Model/FooModel.php
	public $actsAs = array('Pusher.Pusher');

	//Controller/FooController.php
	public $components = array('Auth', 'Pusher.Pusher');

	public $helpers = array('Pusher.Pusher');

	public function push() {
		$data = array(
			'message' => 'Something happened !',
			'triggeredBy' => $this->Auth->user('username')
		);
		$this->Foo->trigger('private-my-great-channel', 'foo_bar', $data);
	}

	public function receive() {

	}

	//View/Foo/receive.ctp
	$this->Pusher->subscribe('private-my-great-channel');
	$this->Pusher->bindEvent('private-my-great-channel', 'foo_bar', 'console.log("An event was triggered by "+data.triggeredBy+" with message "+data.message+);');
	
	//Controller/AppController.php
	protected function _pusherCanAccessPrivateChannel($channelName, $socketId) {
		return (bool) ($this->Auth->user()); // allow access to any channel if user logged in
	}
	

Open your browser and open in one widget yourapp/foo/receive and in an another widget yourapp/foo/push. When you'll push the event you shoul see the message in your javascript console. If not, checks the ajax request to auth/ because you need to be authenticate since you're subscribing to a private channel.

Functionalities not include yet
------------------------------

The auth for presence channel is not handled yet
Get channel infos
Set socket_id to avoid duplicate event
...

