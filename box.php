<?php
$defaultBoxId = '5c68e255a1008400190cd5fa'; // Default box if none provided
$boxId = isset($_GET['box']) ? htmlspecialchars($_GET['box']) : $defaultBoxId;
$url = "https://api.opensensemap.org/boxes/$boxId";

// Fetch API data
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

$latitude = 0;
$longitude = 0;
if (isset($data['currentLocation']['coordinates'])) {
    $longitude = $data['currentLocation']['coordinates'][0];
    $latitude = $data['currentLocation']['coordinates'][1];
}

// Display main box info
echo "<div class='card'>";
echo "<h2>" . htmlspecialchars($data['name']) . "</h2>";
echo "<p><strong>Box ID:</strong> " . htmlspecialchars($data['_id']) . "</p>";
echo "<p><strong>Latitude:</strong> $latitude<br><strong>Longitude:</strong> $longitude</p>";
echo "</div>";

// Display sensors in cards with chart configs
$chartIndex = 0;
if (!empty($data['sensors'])) {
    foreach ($data['sensors'] as $sensor) {
        $title = htmlspecialchars($sensor['title']);
        $unit = $sensor['unit'] ?? '';
        $value = $sensor['lastMeasurement']['value'] ?? 'N/A';
        $time = $sensor['lastMeasurement']['createdAt'] ?? '';

        echo "<div class='card'>";
        echo "<h3>$title</h3>";
        echo "<p><strong>Latest Value:</strong> $value $unit</p>";
        echo "<p><strong>Time:</strong> $time</p>";

        // Simulated historical values
        $numVal = is_numeric($value) ? floatval($value) : 0;
        $values = [
            $numVal - 1.5,
            $numVal - 1,
            $numVal,
            $numVal + 0.5,
            $numVal + 1
        ];
        $labels = ["T-4h", "T-3h", "T-2h", "T-1h", "Now"];

        $chartConfig = [
            'type' => 'line',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => "$title ($unit)",
                    'data' => $values,
                    'borderColor' => 'rgba(75, 192, 192, 1)',
                    'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                    'tension' => 0.3
                ]]
            ],
            'options' => [
                'responsive' => true,
                'plugins' => ['legend' => ['display' => true]]
            ]
        ];

        $jsonConfig = htmlspecialchars(json_encode($chartConfig), ENT_QUOTES, 'UTF-8');
        echo "<div class='chart-container'><canvas class='chart' id='chart$chartIndex' data-chart-config='$jsonConfig'></canvas></div>";
        echo "</div>";

        $chartIndex++;
    }
}

// Map placeholder with coordinates in data attribute
echo "<div class='card'><h3>Sensor Location</h3>";
echo "<div id='map' data-lat='$latitude' data-lon='$longitude'></div></div>";
