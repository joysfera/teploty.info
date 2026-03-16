<?php
$TOKEN="<fill-in-long-lived-access-token-obtained-from-your-Home-Assistant-installation>";
$HOST="homeassistant.local";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://$HOST:8123/api/states");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $TOKEN",
    "Content-Type: application/json"
]);

$response = curl_exec($ch);
$input = json_decode($response, true);
$output = ['HA' => []];

foreach ($input as $item) {
    if (strpos($item['entity_id'], 'sensor.') === 0 && preg_match('/^[0-9-]/', $item['state']) && isset($item['attributes']['unit_of_measurement'])) {
        $output['HA'][] = [
            'i' => hash('fnv1a64', substr($item['entity_id'], strlen('sensor.'))),
            'n' => $item['attributes']['friendly_name'],
            'v' => (float) $item['state'],
            'u' => $item['attributes']['unit_of_measurement']
        ];
    }
}

header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=utf-8');
echo json_encode($output, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
