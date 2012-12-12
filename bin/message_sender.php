<?php

require __DIR__ . "/../vendor/autoload.php";

function showMessage($message) {
    $prefix = ">>> MSG SENDER:";

    if (!is_scalar($message)) {
        $message = var_export($message, true);
    }

    echo "{$prefix} {$message}\n";
}

$configContent = file_get_contents(__DIR__ . "/../config/config.json");
$config = json_decode($configContent);
$logPath = __DIR__ . "/../var/log";
$sentList = array();

$pubnub = new \Pubnub(
    $config->pubnub->publish_key,
    $config->pubnub->subscribe_key,
    $config->pubnub->secret,
    $config->pubnub->ssl
);

$client = new \Mongo("mongodb://{$config->db->host}:{$config->db->port}");
$db = $client->selectDB($config->db->name);

foreach ($config->pubnub->channels as $channel) {

    try {

        $now = new \MongoDate();

        $data = array(
            '$set' => array(
                "channel" => $channel,
            ),

            '$push' => array(
                "history" => array(
                    "sent_at_timestamp" => $now->sec,
                    "sent_at" => $now,
                    "received_at" => null
                )
            )
        );

        $message = array(
            "channel" => $channel,
            "sent_at_timestamp" => $now->sec
        );

        $db->messages->update(
            array('channel' => $channel),
            $data,
            array('safe' => true, 'upsert' => true)
        );

        showMessage("Message stored");

        $pubnub->publish(array(
            "channel" => $channel,
            "message" => $message
        ));

        showMessage("Message sent to channel \"{$channel}\"");
        $sentList[] = $channel;

    } catch (\Exception $e) {

        $data = var_export($e, true);
        $path = $logPath . "/exception.log";
        file_put_contents($path, $data, FILE_APPEND);
    }
}
