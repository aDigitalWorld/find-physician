@extends('frontend.layouts.app')

@section('title', app_name() . ' | ' . __('navs.general.home'))

@section('content')
    <div>
        <form class="form-inline">
            <div class="form-group">
                <label for="raddressInput">Search location:</label>
                <input type="text" class="form-control" style="margin:auto 10px" id="addressInput" size="15"/>
            </div>
            <div class="form-group">
                <label for="radiusSelect">Radius:</label>
                <select class="form-control" id="radiusSelect" style="margin:auto 10px" label="Radius">
                    <option value="150" selected>150 mi</option>
                    <option value="50">50 mi</option>
                    <option value="30">30 mi</option>
                    <option value="20">20 mi</option>
                    <option value="10">10 mi</option>
                </select>
            </div>
            <input type="button" class="btn btn-primary" id="searchButton" value="Search"/>
        </form>
        <form class="form-horizontal" style="margin-top:15px;">
            <div class="form-group">
                <select id="locationSelect" style="visibility: hidden"></select>
            </div>
        </form>
    </div>
    <div id="map" style="width: 100%; height: 90%;min-height: 400px"></div>
    <script>
      var map;
      var markers = [];
      var infoWindow;
      var locationSelect;
      var baseURL = '<?php echo env('APP_URL'); ?>api/';
      setTimeout(function(){ initMap() }, 2000) ;
      document.getElementsByTagName("body")[0].addEventListener("load", initMap);

        function initMap() {
          var sydney = {lat: 39.8333333,lng:-98.585522};
          map = new google.maps.Map(document.getElementById('map'), {
            center: sydney,
            zoom: 6,
            mapTypeId: 'roadmap',
            mapTypeControlOptions: {style: google.maps.MapTypeControlStyle.DROPDOWN_MENU}
          });
          infoWindow = new google.maps.InfoWindow();
          searchButton = document.getElementById("searchButton").onclick = searchLocations;
          locationSelect = document.getElementById("locationSelect");
          locationSelect.onchange = function() {
            var markerNum = locationSelect.options[locationSelect.selectedIndex].value;
            if (markerNum != "none"){
              google.maps.event.trigger(markers[markerNum], 'click');
            }
          };
          setTimeout(function(){
            navigator.geolocation.getCurrentPosition(function(position) {
              // Center on user's current location if geolocation prompt allowed
              var initialLocation = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
              var searchUrl = baseURL + 'search?lat=' + position.coords.latitude + '&lng=' + position.coords.longitude + '&radius=150';
              downloadUrl(searchUrl, function(data) {
                build_markers(data);
                map.setCenter(initialLocation);
                map.setZoom(6);
              });
            }, function(positionError) {
              searchLocationsNear('all');
              map.setCenter(new google.maps.LatLng(39.8097343, -98.5556199));
              map.setZoom(6);
            });
          }, 1000);
        }
       function searchLocations() {
         var address = document.getElementById("addressInput").value;
         var geocoder = new google.maps.Geocoder();
         geocoder.geocode({address: address}, function(results, status) {
           if (status == google.maps.GeocoderStatus.OK) {
            searchLocationsNear(results[0].geometry.location);
           } else {
             alert(address + ' not found');
           }
         });
       }

       function clearLocations() {
         infoWindow.close();
         for (var i = 0; i < markers.length; i++) {
           markers[i].setMap(null);
         }
         markers.length = 0;

         locationSelect.innerHTML = "";
         var option = document.createElement("option");
         option.value = "none";
         option.innerHTML = "See all results:";
         locationSelect.appendChild(option);
       }

       function searchLocationsNear(center) {
         clearLocations();
         if ( 'all' == center ) {
            var searchUrl = baseURL + 'all';
         } else {
            var radius = document.getElementById('radiusSelect').value;
            var searchUrl = baseURL + 'search?lat=' + center.lat() + '&lng=' + center.lng() + '&radius=' + radius;
         }
         downloadUrl(searchUrl, function(data) {
            build_markers(data);
         });
       }

       function build_markers(data) {
          var json = JSON.parse(data);
          var bounds = new google.maps.LatLngBounds();
          for (var i = 0; i < json.length; i++) {
            console.log(json[i]);
            var id = json[i].id;
            var name = json[i].name;
            var address = json[i].formatted_address;
            var distance = parseFloat(json[i].distance);
            var latlng = new google.maps.LatLng( parseFloat(json[i].lat), parseFloat(json[i].lng));
            createOption(name, distance, i);
            createMarker(latlng, name, address);
            bounds.extend(latlng);
          }
          console.log(markers);
          map.fitBounds(bounds);
          map.setZoom(6);
          locationSelect.style.visibility = "visible";
          locationSelect.onchange = function() {
            var markerNum = locationSelect.options[locationSelect.selectedIndex].value;
            google.maps.event.trigger(markers[markerNum], 'click');
          };
       }

       function createMarker(latlng, name, address) {
          var html = "<b>" + name + "</b> <br/>" + address;
          var marker = new google.maps.Marker({
            map: map,
            position: latlng
          });
          google.maps.event.addListener(marker, 'click', function() {
            infoWindow.setContent(html);
            infoWindow.open(map, marker);
          });
          markers.push(marker);
        }

       function createOption(name, distance, num) {
          var option = document.createElement("option");
          option.value = num;
          option.innerHTML = name;
          locationSelect.appendChild(option);
       }

       function downloadUrl(url, callback) {
          var request = window.ActiveXObject ?
              new ActiveXObject('Microsoft.XMLHTTP') :
              new XMLHttpRequest;

          request.onreadystatechange = function() {
            if (request.readyState == 4) {
              request.onreadystatechange = doNothing;
              callback(request.responseText, request.status);
            }
          };

          request.open('GET', url, true);
          request.send(null);
       }

       function doNothing() {}
  </script>
  <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo env('GMAP_API_KEY'); ?>&callback=initMap" async defer></script>

@endsection
