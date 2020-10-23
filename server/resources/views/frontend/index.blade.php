@extends('frontend.layouts.app')

@section('title', app_name() . ' | ' . __('navs.general.home'))

@section('content')

<style type="text/css">

/* map needs width and height to appear */
#map{
    width: 900px;
    max-width: 100%;
    height: 500px;
}

</style>

</head>
<body>

<!-- this div will hold your map -->
<div id="map"></div>

<!-- this div will hold your store info -->
<div id="info_div"></div>

<script>
function initMap() {
    var myMapCenter = {lat: 40.785091, lng: -73.968285};

    // Create a map object and specify the DOM element for display.
    var map = new google.maps.Map(document.getElementById('map'), {
        center: myMapCenter,
        zoom: 14
    });


    function markStore(storeInfo){

        // Create a marker and set its position.
        var marker = new google.maps.Marker({
            map: map,
            position: storeInfo.location,
            title: storeInfo.name
        });

        // show store info when marker is clicked
        marker.addListener('click', function(){
            showStoreInfo(storeInfo);
        });
    }

    // show store info in text box
    function showStoreInfo(storeInfo){
        var info_div = document.getElementById('info_div');
        info_div.innerHTML = 'Store name: '
            + storeInfo.name
            + '<br>Hours: ' + storeInfo.hours;
    }

    var stores = [
        {
            name: 'Store 1',
            location: {lat: 40.785091, lng: -73.968285},
            hours: '8AM to 10PM'
        },
        {
            name: 'Store 2',
            location: {lat: 40.790091, lng: -73.968285},
            hours: '9AM to 9PM'
        }
    ];

    stores.forEach(function(store){
        markStore(store);
    });

}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=<?php echo env('GMAP_API_KEY'); ?>&callback=initMap" async defer></script>

@endsection
