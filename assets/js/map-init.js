let map;
let marker;
let searchBox;

function initMap() {
    // Default location (London)
    const defaultLocation = { lat: 51.5074, lng: -0.1278 };

    // Create map
    map = new google.maps.Map(document.getElementById('map'), {
        center: defaultLocation,
        zoom: 13,
        mapTypeControl: true,
        streetViewControl: false
    });

    // Create marker
    marker = new google.maps.Marker({
        position: defaultLocation,
        map: map,
        draggable: true
    });

    // Create search box
    const input = document.getElementById('location-search');
    searchBox = new google.maps.places.SearchBox(input);

    // Bias SearchBox results towards current map's viewport
    map.addListener('bounds_changed', function() {
        searchBox.setBounds(map.getBounds());
    });

    // Listen for search box changes
    searchBox.addListener('places_changed', function() {
        const places = searchBox.getPlaces();
        if (places.length === 0) return;

        const place = places[0];
        if (!place.geometry || !place.geometry.location) return;

        // Update marker and map
        marker.setPosition(place.geometry.location);
        map.setCenter(place.geometry.location);
        updateLocationInput(place.geometry.location);
    });

    // Listen for marker drag events
    marker.addListener('dragend', function() {
        updateLocationInput(marker.getPosition());
    });

    // Listen for map clicks
    map.addListener('click', function(event) {
        marker.setPosition(event.latLng);
        updateLocationInput(event.latLng);
    });
}

function updateLocationInput(location) {
    document.getElementById('location').value = 
        location.lat().toFixed(6) + ',' + location.lng().toFixed(6);
}

// Handle initialization errors
function handleMapError() {
    console.error('Google Maps failed to load');
    document.getElementById('map').innerHTML = 
        '<p>Failed to load Google Maps. Please try again later.</p>';
}

window.initMap = initMap;
window.handleMapError = handleMapError;