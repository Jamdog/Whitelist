<?php
/**
 * Patreon API Handling Class
 * Version 1.0
 * 
 * File: patreon.class.php
 * Description: Class for retreiving user data from the Patreon API and presenting it in an easy-to-use format
 *
 * @copyright Jamdoggy 2016
 * @author Stefan Cole (aka. Jamdoggy) <jamdog@live.co.uk> and Paul Corey (aka. FamilyCraft_Dad)
 * @license MIT <http://opensource.org/licenses/MIT>
 */
 

class PatreonAPI {
  public  $loggedIn = FALSE;           // TRUE when logged into Patreon using OAuth 
  private $accessToken = NULL;         // Current user access token when logged in  
  private $access = array(             // Config values - should probably be elsewhere
	'client_id'=>'hidden',
	'client_secret'=>'shhhh.  Its a secret',
	'creator_token'=>'Sorry, no creator token here',
	'creator_refresh'=>'Yep, this is hidden too'
    );
  
  /* Function: UpdateToken
   * Here's the OAuth curl bits to pass the specified token data array to login to the Patron API, and decode the response
   */
  private function UpdateToken($data)
  {
      $postUrl = 'https://api.patreon.com/oauth2/token';
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL,$postUrl);
      curl_setopt($ch, CURLOPT_POST,1);
      curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: application/x-www-form-urlencoded'));
      curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $response = curl_exec($ch);
      curl_close($ch);
      $userAuthData = json_decode($response,TRUE);

	  // Grab the current access token then return the data
	  $this->accessToken = $userAuthData['access_token'];
	  return $userAuthData;
  }
  
  /* Function: QueryAPI
   * The API curl bits, to pass a query to the Patreon API and decode it's response
   */
  private function QueryAPI($api_suffix) {
    $api_endpoint = "https://api.patreon.com/oauth2/api/".$api_suffix;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $authorization_header = "Authorization: Bearer " . $this->access_token;
    curl_setopt($ch, CURLOPT_HTTPHEADER, array($authorization_header));
    return json_decode(curl_exec($ch), true);
  }
  
  /* Function: Login
   * Use Oauth to 'connect' the current user to their Patreon account 
   */
  public function Login() {
    $success = FALSE;
	
    if (isset($_GET['code']) && isset($_GET['state'])) {

      //The user has gone off to patreon, signed in, and has given us access.  Score!
      //but now we have to let patreon know that it gave this stuff to the right page
      //we do this via a post request with the data you see in the $data array
      $data = http_build_query(
        array(
            'code'=>$_GET['code'],
            'grant_type'=>'authorization_code',
            'client_id'=>$this::access['client_id'],
            'client_secret'=>$this::access['client_secret'],
            'redirect_uri'=>'http://www.familycraftmc.com/manage/index.php'
        )
      );

      $userAuth = $this->UpdateToken($data);
	  
      if (isset($this->accessToken) && ($this->accessToken != NULL))
        $success = TRUE;
	}
    $this->loggedIn = $success;
	return $success;
  }
  
  /* Function: fetchUser
   * Grab the Patreon details for the currently logged in user
   */
  public function fetchUser() {
    return $this->QueryAPI("current_user");
  }

  /* Function: fetchCampaign_and_patrons
   * Fetch the campaign and patrons info for the currently logged in user
   */
  public function fetchCampaign_and_patrons() {
    return $this->QueryAPI("current_user/campaigns?include=rewards,creator,goals,pledges");
  }

  /* Function: fetchCampaign
   * Retrieve only the campaign info for the currently logged in user
   */
  public function fetchCampaign() {
    return $this->QueryAPI("current_user/campaigns?include=rewards,creator,goals");
  }

  /* Function: fetchCampaignPledges
   * Return the list of pledges made to a specific campaign
   */
  public function fetchCampaignPledges($campaign_id, $page_size, $cursor = null) {
    $url = "campaigns/{$campaign_id}/pledges?page%5Bcount%5D={$page_size}";
    if ($cursor != null) {
      $escaped_cursor = urlencode($cursor);
      $url = $url . "&page%5Bcursor%5D={$escaped_cursor}";
    }
    return $this->QueryAPI($url);
  }  
}

?>