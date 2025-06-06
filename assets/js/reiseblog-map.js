document.addEventListener('DOMContentLoaded', function () {
    var map = L.map('reiseblog-map');

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var redIcon = L.icon({
        iconUrl: '/wp-content/plugins/reiseblog-plugin/assets/images/marker-camper.png',
        iconSize: [40, 40],
        iconAnchor: [20, 40],
        popupAnchor: [0, -40]
    });

    fetch('https://reiseblog.marpas.de/wp-json/reiseblog/v1/position')
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) {
                console.log('No position data available.');
                return;
            }

            var latlngs = data.map(pos => [parseFloat(pos.lat), parseFloat(pos.lon)]);

            // Polyline (Route) zeichnen
            var polyline = L.polyline(latlngs, { color: 'blue' }).addTo(map);

            // Letzten Punkt bestimmen
            var lastPosition = data[data.length - 1];
            var latestLatLng = [parseFloat(lastPosition.lat), parseFloat(lastPosition.lon)];

            // Marker für aktuellen Standort
            var marker = L.marker(latestLatLng, { icon: redIcon }).addTo(map);

            // Datum und Uhrzeit formatieren
            var date = new Date(lastPosition.timestamp);
            var formattedDate = date.toLocaleString('de-DE', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });

            // Tooltip für schnellen Hover
            marker.bindTooltip("Aktueller Standort");

            // Popup für Klick
            marker.bindPopup(`<b>Aktueller Standort</b><br>${formattedDate}`).openPopup();

            // Karte auf Marker zentrieren
            map.setView(latestLatLng, 13);
        })
        .catch(function (error) {
            console.error('Error loading position data:', error);
        });
});
