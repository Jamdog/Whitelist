<?php
/**
 * Main Interface
 * Version 1.0
 * 
 * File: main.php
 * Description: Main interface to manage player list
 *
 * @copyright FamilyCraft_Dad 2015
 * @author Paul Corey (aka. FamilyCraft_Dad) <paul@apcorey.com>
 * @license MIT <http://opensource.org/licenses/MIT>
*/

/* Prerequisites */
	class Main {
		public $config;
		public $user;
		public $page;
		function __construct() {
			$this->config = json_decode(file_get_contents('./config/whitelistconfig.json'),true);
			$this->conn = new PDO('mysql:host='.$this->config['mysql']['host'].';dbname='.$this->config['mysql']['db'],$this->config['mysql']['user'], $this->config['mysql']['pass']);
			if ($this->config['googleAuth']) {
				set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ .'/googleauth/vendor/google/apiclient/src');
				require_once __DIR__ .'/../googleauth/vendor/autoload.php';
				$this->authenticate();
			}
			$this->page();
		}
		public function page() {
			if (isset($_GET['p'])) {
				//standard Page
				//split out the paths
				$pathString = trim ($_GET['p'],'/');
				$paths = explode('/',$pathString);
				$getPageInfo = $this->conn->prepare("SELECT * FROM page WHERE p = :p");
				$getPageInfo->execute(array('p'=>$paths[0]));

				if ($getPageInfo->rowCount() == 0) {
					// page not found
					$this->page = array('title'=>'Page Not Found','p'=>'notfound','content'=>'');
				} else {
					$this->page = $getPageInfo->fetchAll(PDO::FETCH_ASSOC)[0];
				}
				if ($this->page['p'] == 'players') {
					if (isset($paths[1])) {
						$this->page['player'] = $paths[1];
					}
				}
			} else {
				//home page - lets just make it the player page for now
				$this->page = array('title'=>'Home','p'=>'home','content'=>'');
			}
			?>
				<!DOCTYPE html>
				<html>
					<head>
						<title><?=$this->page['title']?></title>
						<meta charset="utf-8">
						<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
						<link href="http://fonts.googleapis.com/css?family=Roboto+Condensed:700,300" rel="stylesheet" type="text/css">
						<link type="text/css" rel="stylesheet" href="<?=$this->config['root']?>css/style.css">
						<link rel="stylesheet" href="<?=$this->config['root']?>font-awesome/css/font-awesome.min.css">
						<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/r/dt/jqc-1.11.3,dt-1.10.9,se-1.0.1/datatables.min.css"/>
						<script type="text/javascript" src="https://cdn.datatables.net/r/dt/jqc-1.11.3,dt-1.10.9,se-1.0.1/datatables.min.js"></script>
						<!--[if lt IE 9]>
							<script src='http://html5shim.googlecode.com/svn/trunk/html5.js'></script>
							<script src='http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js'></script>
						<![endif]-->
						<script src="<?=$this->config['root']?>js/menu.js" type="text/javascript"></script>
						<? if ($this->page['p'] == 'players') { echo '<script src="'.$this->config['root'].'js/players.js" type="text/javascript"></script>'; } ?>
					</head>
					<body>
						<? $this->accountBar(); ?>
						<? $this->mainMenu(); ?>
						<?
							if ($this->page['p'] == 'players') {
								$this->players();
							} elseif ($this->page['p'] == 'groups') {
								$this->groups();
							} elseif ($this->page['p'] == 'addplayer') {
								$this->addPlayer();
							} else {
								$this->standardPage();
							}
						?>
					</body>
				</html>
			<?
		}
		public function accountBar() {
			?>
				<header>

				</header>
			<?
		}
		public function mainMenu() {
			?>
				<nav>
					<ul>
						<li><a href='<?=$this->config['root']?>players/'><i class="fa fa-user"></i> Players</a></li>
						<li><a href='<?=$this->config['root']?>groups/'><i class="fa fa-users"></i> Groups</a></li>
					</ul>
				</nav>
			<?
		}
		public function players() {
			$players = $this->conn->query("SELECT * FROM member ORDER BY ign")->fetchAll(PDO::FETCH_ASSOC);
			?>
				<div class='page'>
					<div class='playersMenu'>
						<ul style='clearfix'>
							<li class='addUser noSelect'><a href='<?=$this->config['root']?>addplayer/'>Add User</a></li>
							<li class='viewUser singleSelect'><a href='<?=$this->config['root']?>'>View Profile</a></li>
							<li class='activate singleSelect'><a href='#'>Activate</a></li>
							<li class='addGroup noLink singleSelect multiSelect'>Add to Group  
								<select name='addGroup'>
									<option value='dud'>Choose One</option>
									<?php 
										$groups = $this->conn->query("SELECT * FROM `group` ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
										foreach ($groups as $group) { 
									?>
										<option value='<?=$group['id']?>'><?=$group['name']?></option>
									<?php } ?>
								</select>
							</li>
							<li class='removeGroup noLink singleSelect multiSelect'>Remove From Group 
								<select name='removeGroup'>
									<option value='dud'>Choose One</option>
									<?php 
										$groups = $this->conn->query("SELECT * FROM `group` ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
										foreach ($groups as $group) { 
									?>
										<option value='<?=$group['id']?>'><?=$group['name']?></option>
									<?php } ?>
								</select>
							</li>
						</ul>
					</div>
					<table class='players'>
						<thead>
							<tr>
								<th>id</th>
								<th>IGN</th>
								<th>UUID</th>
								<th>Email</th>
								<th>Status</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ($players as $player) { ?>
								<tr>
									<td><?=$player['id']?></td>
									<td><?=$player['ign']?></td>
									<td><?=$player['uid']?></td>
									<td><?=$player['email']?></td>
									<td><? if ($player['active'] == 1) { echo "Active"; } else { echo "Not Active"; } ?></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			<?
		}
		public function addPlayer() {

		}
		public function groups() {
			?>
				<div class='page'>
				</div>
			<?
		}
		public function standardPage() {
			?>
				<div class='page'>
					test
				</div>
			<?
		}
		public function authenticate() {
			$client = new Google_Client();
			$client->setApplicationName($this->config['googleApi']['appName']);
			$client->setClientId($this->config['googleApi']['clientId']);
			$client->setClientSecret($this->config['googleApi']['clientSecret']);
			$client->setRedirectUri($this->config['googleApi']['redirectUri']);

			$client->setScopes(array('https://www.googleapis.com/auth/userinfo.email'));

			$plus = new Google_Service_Plus($client);

			if (isset($_REQUEST['logout'])) {
				// if logout drop session
				unset($_SESSION['access_token']);
			}

			if (isset($_GET['code'])) {
				// if code is set then an auth has just been made
				$client->authenticate($_GET['code']);
				$_SESSION['access_token'] = $client->getAccessToken();
				header('Location: http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF']);
			}

			if (isset($_SESSION['access_token'])) {
				// if access_token exists than an auth has been made and the session has been set
				$client->setAccessToken($_SESSION['access_token']);
			}
			if($client->isAccessTokenExpired()) {
				$this->login($client->createAuthUrl());
			}
			if ($client->getAccessToken()) {
				// if that is true, then the authentication has been made, now we can get some info from the user
				$_SESSION['access_token'] = $client->getAccessToken(); // it's good to set this every time, because strangly enough it CAN change
				$me = $plus->people->get('me'); //get user details
				$this->user = array(); //create user array
				$userInfo = $me->toSimpleObject(); // make "$me" a simpler object to traverse in code
				//loop through all set emails to find the "account" email.  This is the email we'll use to check identity
				$email = '';

				foreach ($userInfo->emails as $e) {
					if ($e['type'] == 'account') {
						$email = $e['value'];
					}
				}
				if ($email != '') {
					//check database to see if said email is in the admin or mod group
						//I'm aware this part could possibly be done better, have at it if you'd like
					$checkEmail = $this->conn->prepare("SELECT member.* FROM member_group_link LEFT JOIN member ON member.id = member_group_link.member_id WHERE member.gmail = :email AND member_group_link.group_id = :group_id");
					$checkEmail->execute(array('email'=>$email,'group_id'=>1));
					if ($checkEmail->rowCount() > 0) {
						//we have an admin
						$this->user = $checkEmail->fetchAll(PDO::FETCH_ASSOC)[0];
						$this->user['level'] = 'admin';
					} else {
						$checkEmail->execute(array('email'=>$email,'group_id'=>2));
						if ($checkEmail->rowCount() > 0) {
							//we have a mod
							$this->user = $checkEmail->fetchAll(PDO::FETCH_ASSOC)[0];
							$this->user['level'] = 'mod';
						} else {
							$this->login($client->createAuthUrl());
						}
					}
				} else {
					$this->login($client->createAuthUrl());
				}
			} else {
				$this->login($client->createAuthUrl());
			}
		}
		public function login($authUrl) {
			echo "<a href='".$authUrl."'>Login</a>";
			exit();
		}
	}
?>