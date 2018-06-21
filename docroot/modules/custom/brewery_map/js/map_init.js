function initMap() {



  // Create a map with default with options
  let map = new google.maps.Map(document.getElementById('brewery-map'), {
    zoom: 6,
    scrollwheel: false,
    mapTypeControl: false,
  });

  // Create the InfoWindow (the bubble that opens on the map)
  let infoWindow = new google.maps.InfoWindow();

  // Create the boundaries object (defined by the points on the map)
  let bounds = new google.maps.LatLngBounds();

  // Map source data comes from PHP
  let breweries = drupalSettings.mapData;

  for (let i in breweries) {

    let brewery = breweries[i];

    let icon = "http://maps.google.com/mapfiles/ms/icons/lightblue.png";
    if(brewery.types.indexOf('Brewpub') !== -1) {
      icon = "http://maps.google.com/mapfiles/ms/icons/green.png";
    }
    else if (brewery.types.indexOf('Brewery') !== -1) {
      icon = "http://maps.google.com/mapfiles/ms/icons/blue.png";
    }

    let marker = new google.maps.Marker({
      map: map,
      title: brewery.name,
      icon: new google.maps.MarkerImage(icon),
      position: {
        lat: brewery.coordinates.latitude,
        lng: brewery.coordinates.longitude
      },
    });

    (function (marker, brewery) {
      google.maps.event.addListener(marker, "click", function (e) {
        infoWindow.open(map, marker);
        infoWindow.setContent(
          "<div class='title'>" + this.title + "</div>"
          + "<div class='location'>" + brewery.address.city + ", " + brewery.address.state + "</div>"
        );

      });
    })(marker, brewery);

    bounds.extend(marker.position);
  }

  google.maps.event.addListener(map, "click", function(event) {
    infoWindow.close();
  });

  map.fitBounds(bounds);
}
