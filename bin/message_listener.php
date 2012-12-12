<?php

require __DIR__ . "/../vendor/autoload.php";

function showMessage($message) {
    $prefix = "\n>>> MSG LISTENER -";

    if (!is_scalar($message)) {
        $message = var_export($message, true);
    }

    echo "{$prefix} {$message}";
}

$configContent = file_get_contents(__DIR__ . "/../config/config.json");
$config = json_decode($configContent);

$pubnub = new \Pubnub(
    $config->pubnub->publish_key,
    $config->pubnub->subscribe_key,
    $config->pubnub->secret,
    $config->pubnub->ssl
);


if (isset($argv[1])) {
    $channel = $argv[1];
} else {
    showMessage("Channel to listen: ");
    $channel = trim(fgets(STDIN));
}

showMessage("Listening to channel \"{$channel}\" (will redirect to {$config->udp->host}:{$config->udp->port})");
$pubnub->subscribe(array(
    "channel" => $channel,
    "callback" => function($message) use ($config) {

        $messageBody = $message[0][0];
        $messageExport = var_export($messageBody, true);

        showMessage("Message received!: {$messageExport}");

        $udpSocket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        $messageJson = json_encode($messageBody);
        $messageLength = strlen($messageJson);

        socket_sendto($udpSocket, $messageJson, $messageLength, 0, $config->udp->host, $config->udp->port);
        socket_close($udpSocket);
        showMessage("Message redirected to UDP Server {$config->udp->host}:{$config->udp->port}!");

        $keepListening = true;
        return $keepListening;
    }
));
