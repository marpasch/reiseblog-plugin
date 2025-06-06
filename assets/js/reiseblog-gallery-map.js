document.addEventListener('DOMContentLoaded', function () {
    var map = L.map('reiseblog-gallery-map').setView([51.1657, 10.4515], 6); // Deutschland Mitte

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    fetch(reiseblogGalleryMap.apiUrl)
        .then(response => response.json())
        .then(data => {
            if (data.length === 0) {
                console.log('Keine Bilder gefunden.');
                return;
            }

            var markers = [];

            data.forEach(function (image) {
                var latlng = [parseFloat(image.latitude), parseFloat(image.longitude)];

                var marker = L.marker(latlng).addTo(map);
                marker.bindPopup(
                    '<a href="' + image.image_url + '" data-lightbox="reiseblog-gallery" data-title="' + image.timestamp + '">' +
                        '<img src="' + image.image_url + '" style="max-width: 200px;">' +
                    '</a>'
                );

                markers.push({
                    marker: marker,
                    latlng: latlng,
                    image: image
                });

                // Galerie Thumbnails
                var thumb = document.createElement('img');
                thumb.src = image.image_url;
                thumb.style.width = '100px';
                thumb.style.height = '100px';
                thumb.style.objectFit = 'cover';
                thumb.style.cursor = 'pointer';

                thumb.addEventListener('click', function () {
                    map.setView(latlng, 13);
                    marker.openPopup();
                });

                document.getElementById('reiseblog-gallery-thumbnails').appendChild(thumb);
            });

            // Karte auf alle Marker zoomen
            var group = new L.featureGroup(markers.map(m => m.marker));
            map.fitBounds(group.getBounds());
        })
        .catch(error => {
            console.error('Fehler beim Laden der Galerie-Daten:', error);
        });
});
