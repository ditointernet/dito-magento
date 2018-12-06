<?php

class Dito_DitoTracking_Model_Utilities_DitoIns
{

  /** @var string Platform Api Key */
  private $platform_api_key = '';

  /** @var string App Secret */
  private $sha1_signature = '';

  /** @var string  Event path. def https://events.plataformasocial.com.br/users/ */
  private $event_url = 'https://events.plataformasocial.com.br/users/';

  /** @var string  Login path. def https://login.plataformasocial.com.br/users/portal/ */
  private $login_url = 'https://login.plataformasocial.com.br/users/portal/';

  /** @var string  User id. */
  private $id = '';

  /** @var string  Url with id. */
  private $url = '';

  /** @var array  Prepared request array */
  private $preparedpack = array();

  /** @var Closure Closure method called on error */
  private $errorHandle = null;

  /** @var Closure Closure method called on success */
  private $readyHandle = null;

  /** @var Array Last request information */
  private $lastRequest = null;

  /**
   * Construct new Dito Request instace
   * @param string $app_key Api Key 
   * @param string $app_secret Secret
   * @since 1.0.0
   */
  function __construct($app_key, $app_secret, $reference)
  {
    $this->platform_api_key = $app_key;
    $this->sha1_signature = sha1($app_secret);
    $this->id = $reference;
  }

  public function prepare_event($mid, $event) 
  {
    $this->preparedpack = array(
      "id_type" => "id",
      "platform_api_key" => $this->platform_api_key,
      "sha1_signature" => $this->sha1_signature,
      "message_id" => $mid,
      "event" => json_encode($event)
    );
    $this->url = $this->event_url . $this->id;

    return $this;
  }

  public function prepare_identify($user_data) 
  {
    $this->preparedpack = array(
      "id_type" => "id",
      "platform_api_key" => $this->platform_api_key,
      "sha1_signature" => $this->sha1_signature,
      "user_data" => $user_data
    );
    $this->url = $this->login_url . $this->id . '/signup';
    
    return $this;
  }

  /**
   * 
   * Force send prepared data
   * @since 1.0.0
   */
  public function send() 
  {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $this->url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $this->preparedpack);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 1);
    $data = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $report = curl_getinfo($ch);
    curl_close($ch);
    $this->lastRequest = array("code" => $httpcode, "response" => $data, "info" => $report, 'fields' => $this->preparedpack);
    if (($httpcode !== 200) && ($this->errorHandle !== null)) {
      call_user_func_array($this->errorHandle, array($this));
    } elseif (($httpcode === 200) && ($this->readyHandle !== null)) {
      call_user_func_array($this->readyHandle, array($this));
    }
  }

  /**
   * Return last request as array (debug)
   * @return array
   * @since 1.0.0
   */
  public function getLastRequest()
  {
    return $this->lastRequest;
  }

  /**
   * Set Callbacks for error action and ready action
   * @param \Closure $errorHandle
   * @param \Closure $readyHandle
   * @since 1.0.0
   */
  public function setCallbacks($errorHandle = null, $readyHandle = null)
  {
    $this->errorHandle = $errorHandle;
    $this->readyHandle = $readyHandle;
  }
}

