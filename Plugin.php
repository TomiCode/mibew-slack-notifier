<?php

namespace Mibew\Mibew\Plugin\SlackNotifier;

use Mibew\EventDispatcher\EventDispatcher;
use Mibew\EventDispatcher\Events;
use Mibew\Thread;

class Plugin extends \Mibew\Plugin\AbstractPlugin implements \Mibew\Plugin\PluginInterface
{
  protected $config;

  protected $initialized = false;

  public function __construct($config)
  {
    if (empty($config['webhook_url'])) {
      trigger_error('Slack Webhook URL cannot be empty', E_USER_ERROR);
      return;
    }

   $this->initialized = true;
   $this->config = $config + array(
     'channel' => NULL,
     'username' => NULL,
     'message' => "A new visitor is waiting for an answer."
   );
  }

  public function initialized()
  {
    return $this->initialized;
  }

  public function run()
  {
    $dispatcher = EventDispatcher::getInstance();
    $dispatcher->attachListener(Events::THREAD_USER_IS_READY, $this, 'threadUserIsReady');
  }

  public function threadUserIsReady(&$args)
  {
    $thread = $args['thread'];
    if ($thread->userId /* && invitationState == INVITATION_NOT_INVITED */ ) {
      $this->sendSlackMessage();
    }
  }

  private function sendSlackMessage()
  {
    $data_fields = [ 'text' => $config['message'] ];
    if ($channel = $config['channel'])
      $data_fields['channel'] = $channel;

    if ($username = $config['username'])
      $data_fields['username'] = $username;

    $ch = curl_init($config['webhook_url']);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, ['payload' => json_encode($data_fields)]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    curl_close($ch);

    return ($result == "ok")
  }

  public static function getVersion()
  {
    return '1.0.0';
  }

  public static function getSystemRequirements()
  {
    return array( 'mibew' => '^2.1.0' );
  }
}
