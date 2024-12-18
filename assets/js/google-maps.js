function initMap() {
  const map = new google.maps.Map(document.getElementById('map'), {
    center: { lat: -25.344, lng: 131.031 },
    zoom: 4,
  });

  const marker = new google.maps.Marker({
    position: { lat: -25.344, lng: 131.031 },
    map: map,
    title: 'Uluru',
  });

  const input = document.getElementById('location-search');
  const searchBox = new google.maps.places.SearchBox(input);

  map.addListener('bounds_changed', function() {
    searchBox.setBounds(map.getBounds());
  });

  searchBox.addListener('places_changed', function() {
    const places = searchBox.getPlaces();

    if (places.length == 0) {
      return;
    }

    const place = places[0];
    marker.setPosition(place.geometry.location);
    map.setCenter(place.geometry.location);
    document.getElementById('location').value = place.geometry.location.lat() + ',' + place.geometry.location.lng();
  });

  map.addListener('click', function(event) {
    marker.setPosition(event.latLng);
    document.getElementById('location').value = event.latLng.lat() + ',' + event.latLng.lng();
  });
}