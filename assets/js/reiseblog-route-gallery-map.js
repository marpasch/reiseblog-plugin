document.addEventListener('DOMContentLoaded', function () {
    var map = L.map('reiseblog-route-gallery-map').setView([51.1657, 10.4515], 6); // Deutschland Mitte

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    var markerCluster = L.markerClusterGroup();

    var routeData = [];
    var galleryData = [];

    var filterContainer = document.createElement('div');
    filterContainer.id = 'reiseblog-filter-container';
    filterContainer.style.textAlign = 'center';
    filterContainer.style.margin = '20px 0';

    var dateFilter = document.createElement('input');
    dateFilter.type = 'text';
    dateFilter.id = 'reiseblog-date-filter';
    dateFilter.style.padding = '5px';
    dateFilter.style.fontSize = '16px';

    filterContainer.appendChild(dateFilter);

    var mapContainer = document.getElementById('reiseblog-route-gallery-map');
    mapContainer.parentNode.insertBefore(filterContainer, mapContainer);

    var thumbnailsContainer = document.createElement('div');
    thumbnailsContainer.id = 'reiseblog-gallery-thumbnails';
    thumbnailsContainer.style.marginTop = '20px';
    thumbnailsContainer.style.display = 'flex';
    thumbnailsContainer.style.flexWrap = 'wrap';
    thumbnailsContainer.style.gap = '10px';
    mapContainer.after(thumbnailsContainer);

    Promise.all([
        fetch(reiseblogRouteGalleryMap.positionApiUrl).then(response => response.json()),
        fetch(reiseblogRouteGalleryMap.galleryApiUrl).then(response => response.json())
    ])
    .then(([route, gallery]) => {
        console.log('Geladene Route-Daten:', route);
        console.log('Geladene Galerie-Daten:', gallery);

        routeData = route;
        galleryData = gallery;

        var availableDates = new Set();

        routeData.forEach(pos => {
            if (pos.timestamp) {
                const localDate = pos.timestamp.split(' ')[0];
                console.log('Route Date:', localDate, 'Original Timestamp:', pos.timestamp);
                availableDates.add(localDate);
            }
        });

        galleryData.forEach(img => {
            if (img.timestamp) {
                const imgDate = img.timestamp.split(' ')[0];
                console.log('Gallery Date:', imgDate);
                availableDates.add(imgDate);
            }
        });

        flatpickr("#reiseblog-date-filter", {
            dateFormat: "Y-m-d",
            enable: Array.from(availableDates),
            onChange: function(selectedDates, dateStr, instance) {
                renderData(dateStr);
            }
        });

        renderData();
    })
    .catch(error => {
        console.error('Fehler beim Laden der Daten:', error);
    });

    function renderData(selectedDate) {
        map.eachLayer(function (layer) {
            if (layer instanceof L.Polyline || layer instanceof L.Marker || layer instanceof L.LayerGroup) {
                map.removeLayer(layer);
            }
        });

        markerCluster.clearLayers();
        thumbnailsContainer.innerHTML = '';

        var filteredRoute = selectedDate ?
            routeData.filter(pos => pos.timestamp && pos.timestamp.split(' ')[0] === selectedDate) :
            routeData;

        var filteredGallery = selectedDate ?
            galleryData.filter(img => img.timestamp && img.timestamp.split(' ')[0] === selectedDate) :
            galleryData;

        if (filteredRoute.length > 0) {
            var latlngs = filteredRoute.map(pos => [parseFloat(pos.lat), parseFloat(pos.lon)]);

            var routePolyline = L.polyline(latlngs, { color: 'blue' }).addTo(map);

            var lastPosition = filteredRoute[filteredRoute.length - 1];
            var latestLatLng = [parseFloat(lastPosition.lat), parseFloat(lastPosition.lon)];

            var redIcon = L.icon({
                iconUrl: '/wp-content/plugins/reiseblog-plugin/assets/images/marker-camper.png',
                iconSize: [40, 40],
                iconAnchor: [20, 40],
                popupAnchor: [0, -40]
            });

            L.marker(latestLatLng, { icon: redIcon }).addTo(map).bindPopup("Aktueller Standort");

            map.fitBounds(routePolyline.getBounds());
        }

        filteredGallery.forEach(function (image) {
            var latlng = [parseFloat(image.latitude), parseFloat(image.longitude)];

            var marker = L.marker(latlng);
            marker.bindPopup(
                '<a href="' + image.image_url + '" data-lightbox="reiseblog-gallery" data-title="' + image.timestamp + '">' +
                    '<img src="' + image.image_url + '" style="max-width: 200px;">' +
                '</a>'
            );

            markerCluster.addLayer(marker);

            var thumb = document.createElement('img');
            thumb.src = image.image_url;
            thumb.loading = 'lazy';
            thumb.style.width = '100px';
            thumb.style.height = '100px';
            thumb.style.objectFit = 'cover';
            thumb.style.cursor = 'pointer';

            thumb.addEventListener('mouseover', function () {
                thumb.style.transform = 'scale(1.1)';
                thumb.style.transition = 'transform 0.3s';
            });

            thumb.addEventListener('mouseout', function () {
                thumb.style.transform = 'scale(1)';
            });

            thumb.addEventListener('click', function () {
                map.setView(latlng, 13);
                marker.openPopup();
            });

            thumbnailsContainer.appendChild(thumb);
        });

        map.addLayer(markerCluster);
    }
});
