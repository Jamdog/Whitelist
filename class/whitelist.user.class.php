<?php
/**
 * Whitelist Player Finder
 * Version 1.0
 * 
 * File: whitelist.user.class.php
 * Description: User Class for retreiving data from various APIs into one place
 *
 * @copyright Jamdoggy 2015
 * @author Stefan Cole (aka. Jamdoggy) <jamdog@live.co.uk>
 * @license MIT <http://opensource.org/licenses/MIT>
 */
class whitelistUser {
  public $userData;     // Data array
  public $userIGN=NULL; // The current player IGN/Username
  public $e = NULL;     // Error message array (if not NULL, an error occurred)

  function __construct($ign=NULL)
  {
    if (isset($ign) && ($ign != NULL)) {
	  $this->userIGN = $ign;
	  $this->getUserData($ign);
	}
  }

  function __destruct() { }

  // Overloading
  public function __set($name, $value) {
	$this->userData[$name] = $value;
  }

  public function __get($name) {
	if (array_key_exists($name, $this->userData)) {
	  return $this->userData[$name];
    }
    // Invalid array key specified
    $trace = debug_backtrace();
    trigger_error(
        'Undefined property via __get(): ' . $name .
        ' in ' . $trace[0]['file'] .
        ' on line ' . $trace[0]['line'],
        E_USER_NOTICE);
    return NULL;
  }
  
  private function getPremiumStatus($uid=NULL) {
    if (!isset($uid) || ($uid == NULL)) {
      $uid = $this->userIGN;
    }
    if (isset($uid) && ($uid != NULL)) {
      $this->userData['premium'] = @file_get_contents('https://minecraft.net/haspaid.jsp?user=' . $uid);
      if ($this->userData['premium'] != 'true') {
        if ($this->userData['premium'] == 'false') {
          die('Error: Player does not have a premium minecraft account.');
        } else {
          $this->userData['premium'] = 'false';
          $this->e['title'] = "Warning: Unable to determine if player is premium";
          $this->e['text']  = "The Mojang server responsible for account validation may be offline or not responding.<br />";
          $this->e['text'] .= "Press F5 to refresh, if you wish to validate the player's account.";
          $this->e['code']  = ERR_API_INVALID_REPLY;
        }
      }
    } else {
      $this->userData['premium'] = 'false';
      $this->e['title'] = "Warning: Invalid Username or IGN not set";
      $this->e['text']  = "Player IGN has not been set, or username not passed to getPremiumStatus.<br />";
      $this->e['text'] .= "Notify site owner to fix this issue.";
      $this->e['code']  = ERR_INTERNAL_INVALID_IGN;
	}
  } // end getPremiumStatus

  private function getUUID($uid=NULL) {
    if (!isset($uid) || ($uid == NULL)) {
      $uid = $this->userIGN;
    }
    if (isset($uid) && ($uid != NULL)) {
      $uuidData = @file_get_contents('https://api.mojang.com/users/profiles/minecraft/' . $uid);
      if (!isset($uuidData)) {
        $this->userData['UUID'] = NULL;
        $this->e['title'] = "Warning: Unable to retrieve player UUID";
        $this->e['text']  = "The UUID value could not be retrieved from Mojang's API for <b>".$uid."</b>.<br />";
        $this->e['text'] .= "Press F5 to refresh, if you wish to tryy again.";
        $this->e['code']  = ERR_API_UUID_FAILURE;
      } else {
        $uuidData = json_decode($uuidData, true);
	    $this->userData['UUID'] = $uuidData['id'];
      }
    } else {
      $this->userData['UUID'] = NULL;
      $this->e['title'] = "Warning: Invalid Username or IGN not set";
      $this->e['text']  = "Player IGN has not been set, or username not passed to getUUID.<br />";
      $this->e['text'] .= "Notify site owner to fix this issue.";
      $this->e['code']  = ERR_INTERNAL_INVALID_IGN;
	}
  } // end getUUID
  
  private function getPrevIGNList($uuid=NULL) {
    if (!isset($uuid) || ($uuid == NULL)) {
      $uuid = $this->userData['UUID'];
    }
    if (isset($uuid) && ($uuid != NULL)) {
      $ignData = @file_get_contents('https://api.mojang.com/user/profiles/' . $uuid . '/names');
      if (!isset($ignData)) {
        $this->userData['ignList'] = NULL;
        $this->e['title'] = "Warning: Unable to retrieve previous player IGN list";
        $this->e['text']  = "The list of previous IGNs could be be retrieved from Mojang's API for <b>".$uid."</b>.<br />";
        $this->e['text'] .= "Press F5 to refresh, if you wish to tryy again.";
        $this->e['code']  = ERR_API_IGNLIST_FAILURE;
      } else {
        $this->userData['ignList'] = json_decode($ignData, true);
      }
    } else {
      $this->userData['ignList'] = NULL;
      $this->e['title'] = "Warning: Invalid UUID or UUID not set";
      $this->e['text']  = "Player UUID has not been set, or could not be passed to getPrevIGNList.<br />";
      $this->e['text'] .= "Notify site owner to fix this issue.";
      $this->e['code']  = ERR_INTERNAL_INVALID_UUID;
	}
  } // end getPrevIGNList

  private function getUserData($uid=NULL) {
    if (!isset($uid) || ($uid == NULL)) {
      $uid = $this->userIGN;
    }
    // Get player data from the Wynncraft api and decode it
    if (isset($uid) && ($uid != NULL)) {
      $apiURL = 'http://api.wynncraft.com/public_api.php?action=playerStats&command=' . $uid;
      $playerData = @file_get_contents($apiURL);
      if (empty($playerData) || !isset($playerData)) {
        $this->e['title'] = "Warning: Wynncraft API returned no data";
        $this->e['text']  = "The Wynncraft API returned no information about <b>".$uid."</b>.<br />";
        $this->e['text'] .= "Either user isn't a Wynncraft player, or the API is offline.";
        $this->e['code']  = ERR_API_NO_DATA;
		// Try to grab the other bits anyway
		$this->getPremiumStatus($uid);
		if ($this->userData['premium'] == 'true') {
		  $this->getUUID($uid);
          if (isset($this->userData['UUID'])) {
		    $this->getPrevIGNList($this->userData['UUID']);
		  }
		}
      } else {
	    // Populate the main array
        $this->userData = json_decode($playerData, true);
		// And now get the other bits
		$this->getPremiumStatus($uid);
		if ($this->userData['premium'] == 'true') {
		  $this->getUUID($uid);
          if (isset($this->userData['UUID'])) {
		    $this->getPrevIGNList($this->userData['UUID']);
		  }
		}
      }
    }
  } // end getUserData
  
  public function getClasses()
  {
    return $this->userData['classes'];
  } // end getClasses

  public function getGlobal()
  {
    return $this->userData['global'];
  } // end getGlobal

  public function getServer()
  {
    return $this->userData['current_server'];
  } // end getServer

  public function getRank()
  {
    return $this->userData['rank'];
  } // end getRank
} // end class
?>