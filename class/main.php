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
		function __construct($justAuth = false) {
			$this->config = json_decode(file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR .'../config/whitelistconfig.json'),true);
			$this->conn = new PDO('mysql:host='.$this->config['mysql']['host'].';dbname='.$this->config['mysql']['db'],$this->config['mysql']['user'], $this->config['mysql']['pass']);
			if ($this->config['googleAuth']) {
				set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ .'/googleauth/vendor/google/apiclient/src');
				require_once __DIR__ .'/../googleauth/vendor/autoload.php';
				$this->authenticate();
			}
			if (!$justAuth) {
				$this->page();
			}
		}
		public function page() {
			if (!isset($_GET['p'])) {
				$_GET['p'] = 'home';
			}
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
			if (isset($paths[1])) {
				$this->page['subPage'] = $paths[1];
			} else {
				$this->page['subPage'] = '';
			}
			if ($this->page['subPage'] == 'addplayer' && isset($_POST['submit'])) {
				//New user submitted
				//first bring in jam's class
				include ("./class/whitelist.user.class.php");
				$_POST['ign'] = filter_var($_POST['ign'],FILTER_SANITIZE_STRING);
				$_POST['email'] = filter_var($_POST['email'],FILTER_SANITIZE_STRING);
				$player = new whitelistUser($_POST['ign']);
				if (is_null($player->e)) {
					//we've got a live player
					$player->userData['formattedUUID'];
					//check if they're already on the server
					$checkPlayer = $this->conn->prepare("SELECT * FROM member WHERE uid = :uid");
					$checkPlayer->execute(array('uid'=>$player->userData['formattedUUID']));
					if ($checkPlayer->rowCount() > 0) {
						//oops the player already exists, maybe their ign has changed though, so just update the ign and be done with it
						$updatePlayer = $this->conn->prepare("UPDATE member SET ign = :ign WHERE uid = :uid");
						//$updatePlayer->execute(array('ign'=>$player->userData['ign'],'uid'=>$player->userData['formattedUUID']))
					} else {
						//cool, they don't exist, drop em in there bro!
						$insertPlayer = $this->conn->prepare("INSERT INTO member (`ign`,`uid`,`email`,`active`) VALUES (:ign,:uid,:email,1)");
						$insertPlayer->execute(array('ign'=>$player->userIGN,'uid'=>$player->userData['formattedUUID'],'email'=>$_POST['email']));
					}
				} else {
					//oops, there's been an error, display the error's title... maybe more in the future, but that'll do for now
					$this->issues['ign'] = $player->e['title'];
				}
			}
			if ($this->page['p'] == 'players' && $this->page['subPage'] != '' && isset($_POST['submit'])) {
				//updating profile
				//get user id
				$ign = $this->page['subPage'];
				$getUser = $this->conn->prepare("SELECT * FROM member WHERE ign = :ign");
				$getUser->execute(array('ign'=>$ign));
				if ($getUser->rowCount() > 0) {
					$user = $getUser->fetchAll()[0];
					//check activation
					if (isset($_POST['active'])) {
						$active = 1;
					} else {
						$active = 0;
					}
					$_POST['email'] = filter_var($_POST['email'],FILTER_SANITIZE_EMAIL);
					$updateMember = $this->conn->prepare("UPDATE member SET active = :active,email = :email WHERE id = :id");
					$updateMember->execute(array('active'=>$active,'email'=>$_POST['email'],'id'=>$user['id']));
					//set group states
					$groups = $this->conn->query("SELECT id FROM `group`")->fetchAll(PDO::FETCH_ASSOC);
					$checkGroupState = $this->conn->prepare("SELECT * FROM member_group_link WHERE member_id = :member_id AND group_id = :group_id");
					$insertGroup = $this->conn->prepare("INSERT INTO member_group_link (member_id,group_id) VALUES (:member_id,:group_id)");
					$removeGroup = $this->conn->prepare("DELETE FROM member_group_link WHERE member_id = :member_id AND group_id = :group_id");
					foreach ($groups as $group) {
						//first check it's state
						if (isset($_POST['g'.$group['id']])) {
							//see if group link already exists first, if not add it
							$checkGroupState->execute(array('member_id'=>$user['id'],'group_id'=>$group['id']));
							if ($checkGroupState->rowCount() == 0) {
								//go ahead and insert it
								$insertGroup->execute(array('member_id'=>$user['id'],'group_id'=>$group['id']));
							}
						} else {
							//remove it (if it doesn't already exist, nothing happens here)
							$removeGroup->execute(array('member_id'=>$user['id'],'group_id'=>$group['id']));
						}
					}
				}
				
			}
			?>
				<!DOCTYPE html>
				<html>
					<head>
						<title><?=$this->page['title']?></title>
						<meta charset="utf-8">
						<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
						<link href="http://fonts.googleapis.com/css?family=Roboto+Condensed:700,300" rel="stylesheet" type="text/css">
						<link rel="shortcut icon" href="<?=$this->config['root']?>favicon.ico" />
						<link type="text/css" rel="stylesheet" href="<?=$this->config['root']?>css/style.css">
						<link rel="stylesheet" href="<?=$this->config['root']?>font-awesome/css/font-awesome.min.css">
						<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/r/dt/jqc-1.11.3,dt-1.10.9,se-1.0.1/datatables.min.css"/>
						<script type="text/javascript" src="https://cdn.datatables.net/r/dt/jqc-1.11.3,dt-1.10.9,se-1.0.1/datatables.min.js"></script>
						<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">
						<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
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
			$groups = $this->conn->query("SELECT * FROM `group` ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
			$getPlayerGroups = $this->conn->prepare("SELECT `group_id` FROM member_group_link WHERE member_id = :member_id");
			?>
				<div class='page'>
					<?php
						if ($this->page['subPage'] != '') {
							//there's a subpage
							if ($this->page['subPage'] == 'addplayer') {
								$this->addPlayer();
							} else {
								$this->profile($this->page['subPage']);
							}
							echo "<h1>Player List</h1>";
						}
					?>
					<div class='playersMenu'>
						<ul style='clearfix'>
							<li class='addUser noSelect'><a href='<?=$this->config['root']?>players/addplayer/'>Add Player</a></li>
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
								<th>Email</th>
								<th>Active</th>
								<?php
									foreach ($groups as $group) {
										echo "<th>".$group['name']."</th>";
									}
								?>
							</tr>
						</thead>
						<tbody>
							<?php 
								foreach ($players as $player) { 
									$getPlayerGroups->execute(array('member_id'=>$player['id']));
									$playerGroups = $getPlayerGroups->fetchAll(PDO::FETCH_COLUMN);
									?>
										<tr data-playerId='<?=$player['id']?>'>
											<td><?=$player['id']?></td>
											<td><?=$player['ign']?></td>
											<td><?=$player['email']?></td>
											<td class='activeCell'><? if ($player['active'] == 1) { echo "<i class='fa fa-check'></i>"; } ?></td>
											<?php
												foreach ($groups as $group) {
													echo "<td data-groupid=".$group['id'].">";
													if (in_array($group['id'],$playerGroups)) {
														echo "<i class='fa fa-check'></i>";
													}
													echo "</td>";
												}
											?>
										</tr>
									<?php 
								} 
							?>
						</tbody>
					</table>
				</div>
			<?
		}
		public function addPlayer() {
			?>
				<h1>Add Player</h1>
				<form method='POST' action= '<?=$this->config['root']?>players/addplayer/' class='clearfix'>
					<fieldset>
						<div class='label'>Email</div>
						<input type='text' name='email' value='' />
						<div class='label'>IGN</div>
						<input type='text' name='ign' value='' />
					</fieldset>
					<input class='submit' type='submit' name='submit' value='Add' />
				</form>
			<?
		}
		public function profile($ign) {
			$groups = $this->conn->query("SELECT * FROM `group` ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
			$getPlayerInfo = $this->conn->prepare("SELECT * FROM member WHERE ign = :ign");
			$getPlayerInfo->execute(array('ign'=>$ign));
			$player = $getPlayerInfo->fetchAll(PDO::FETCH_ASSOC)[0];
			$getPlayerGroups = $this->conn->prepare("SELECT `group_id` FROM member_group_link WHERE member_id = :member_id");
			$getPlayerGroups->execute(array('member_id'=>$player['id']));
			$playerGroups = $getPlayerGroups->fetchAll(PDO::FETCH_COLUMN);
			$getWarnings = $this->conn->prepare("SELECT *,DATE_FORMAT(original,'%Y-%m-%d') as original,DATE_FORMAT(updated,'%Y-%m-%d') as updated FROM warnings WHERE member_id = :id");
			$getWarnings->execute(array('id'=>$player['id']));
			$warnings = $getWarnings->fetchAll(PDO::FETCH_ASSOC);
			?>
				<h1><?=$player['ign']?></h1>
				<div class='clearfix'>
					<div class='third'>
						<form method='POST' action= '<?=$this->config['root']?>players/<?=$this->page['subPage']?>/' class='clearfix'>
							<fieldset>
								<div class='label'>Email</div>
								<input type='text' name='email' value='<?=$player['email']?>' />
							</fieldset>
							<fieldset>
								<ul class='checkBoxList'>
									<li>
										<input type='checkbox' name='active'<?php if ($player['active'] == 1) { echo " checked"; } ?> />Active
									</li>
									<?php
										foreach ($groups as $group) {
											echo "<li>";
											echo "<input type='checkbox' name='g".$group['id']."'";
											if (in_array($group['id'],$playerGroups)) {
												echo " checked";
											}
											echo " />";
											echo $group['name'];
											echo "</li>";
										}
									?>
								</ul>
							</fieldset>
							<input class='submit' type='submit' name='submit' value='Update' />
						</form>
					</div>
					<div class='twoThirds'>
						<fieldset>
							<div class='label'>Players attached to this player</div>
							<p>You'll be able to manage player relationships here (ex: FamilyCraft_Mom FamilyCraft_Dad and HobbyFan are all on one account)</p>
						</fieldset>
						<fieldset>
							<div class='label'>Warnings/Messages</div>
							<table class='warningList'>
								<thead>
									<tr>
										<th>Original</th><th>Updated</th><th>Message</th><th>Severity</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($warnings as $key => $warning) { ?>
										<tr data-row='<?=$warning['id']?>'>
											<td class='date orgDate'><span><?=$warning['original']?></span><input type='text' value='<?=$warning['original']?>' /></td>
											<td class='date'><?=$warning['updated']?></td>
											<td class='warning' contenteditable>
												<?=$warning['reason']?>
											</td>
											<td class='severity' contenteditable>
												<?=$warning['severity']?>
											</td>
										</tr>
									<?php } ?>
									<tr data-row='new'>
										<td class='date orgDate'><span><?=date("Y-m-d")?></span><input type='text' value='<?=date("Y-m-d")?>' /></td>
										<td class='date'></td>
										<td class='warning' contenteditable></td>
										<td class='severity' contenteditable></td>
									</tr>
								</tbody>
							</table>
						</fieldset>
						<input data-memid='<?=$player['id']?>' class='submit messageSubmit' type='submit' name='submit' value='Update Messages' />
					</div>
				</div>
			<?php
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
					<?=$this->page['content']?>
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