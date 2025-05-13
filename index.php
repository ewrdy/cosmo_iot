<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OpenSenseMap Viewer</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        nav ul {
            list-style: none;
            padding: 0;
        }
        nav ul li {
            display: inline-block;
            margin-right: 15px;
        }
        .card {
            border: 1px solid #ccc;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 2px 2px 6px rgba(0,0,0,0.1);
        }
        .card h3 {
            margin-top: 0;
        }
        .chart-container {
            width: 100%;
            height: 300px;
        }
        #map {
            height: 400px;
            width: 100%;
            margin-top: 30px;
        }
        #countdown {
            font-weight: bold;
            color: green;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php include 'menu.php'; ?>
    <h1>OpenSenseMap Sensor Box</h1>
    <p id="countdown">Refreshing in 15 seconds...</p>
    <div id="box-data"></div>

    <!-- Leaflet & AJAX scripts -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let countdown = 15;
        let chartObjects = [];

        function updateCountdown() {
            document.getElementById('countdown').textContent = `Refreshing in ${countdown} second${countdown !== 1 ? 's' : ''}...`;
            countdown--;
            if (countdown < 0) {
                countdown = 15;
                loadBoxData();
            }
        }
        setInterval(updateCountdown, 1000);

        function loadBoxData() {
            fetch('box.php')
                .then(response => response.text())
                .then(html => {
                    document.getElementById('box-data').innerHTML = html;

                    // Destroy previous charts
                    chartObjects.forEach(chart => chart.destroy());
                    chartObjects = [];

                    // Re-initialize charts
                    document.querySelectorAll('canvas.chart').forEach(canvas => {
                        const config = JSON.parse(canvas.dataset.chartConfig);
                        chartObjects.push(new Chart(canvas, config));
                    });

                    // Re-init map
                    const lat = parseFloat(document.getElementById('map').dataset.lat);
                    const lon = parseFloat(document.getElementById('map').dataset.lon);
                    const map = L.map('map').setView([lat, lon], 15);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(map);
                    L.marker([lat, lon]).addTo(map)
                        .bindPopup('SenseBox Location')
                        .openPopup();
                });
        }

        window.onload = loadBoxData;
    </script>
</body>
</html>
