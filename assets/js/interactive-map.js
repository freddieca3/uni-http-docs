// Initialize and add the map
let map;
let marker;
let searchBox;

async function initMap() {
  // The location of Uluru
  const position = { lat: -25.344, lng: 131.031 };
  // Request needed libraries.
  //@ts-ignore
  const { Map } = await google.maps.importLibrary("maps");
  const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");

  // The map, centered at Uluru
  map = new Map(document.getElementById("map"), {
    zoom: 4,
    center: position,
    mapId: "DEMO_MAP_ID",
  });

  // The marker, positioned at Uluru
  marker = new AdvancedMarkerElement({
    map: map,
    position: position,
    title: "Uluru",
  });

  const input = document.getElementById('location-search');
  searchBox = new google.maps.places.SearchBox(input);

  map.addListener('bounds_changed', function() {
    searchBox.setBounds(map.getBounds());
  });

  searchBox.addListener('places_changed', function() {
    const places = searchBox.getPlaces();

    if (places.length == 0) {
      return;
    }

    const place = places[0];
    if (marker) {
      marker.setPosition(place.geometry.location);
    } else {
      marker = new google.maps.Marker({
        position: place.geometry.location,
        map: map
      });
    }
    map.setCenter(place.geometry.location);
    document.getElementById('location').value = place.geometry.location.lat() + ',' + place.geometry.location.lng();
  });

  map.addListener('click', function(event) {
    placeMarker(event.latLng);
  });
}

function placeMarker(location) {
  if (marker) {
    marker.setPosition(location);
  } else {
    marker = new google.maps.Marker({
      position: location,
      map: map
    });
  }
  document.getElementById('location').value = location.lat() + ',' + location.lng();
}

initMap();