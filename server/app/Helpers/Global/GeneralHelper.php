<?php
use App\Models\Accounts;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;

if (! function_exists('app_name')) {
    /**
     * Helper to grab the application name.
     *
     * @return mixed
     */
    function app_name()
    {
        return config('app.name');
    }
}

if (! function_exists('gravatar')) {
    /**
     * Access the gravatar helper.
     */
    function gravatar()
    {
        return app('gravatar');
    }
}

if (! function_exists('home_route')) {
    /**
     * Return the route to the "home" page depending on authentication/authorization status.
     *
     * @return string
     */
    function home_route()
    {
        if (auth()->check()) {
            if (auth()->user()->can('view backend')) {
                return 'admin.dashboard';
            }

            return 'frontend.user.dashboard';
        }

        return 'frontend.index';
    }
}

if (! function_exists('formatAddress')) {
    function formatAddress($street, $city=false, $state=false, $zipcode=false, $country=false)
    {
        if (is_object($street)) {
            $account = $street;
            $street = $account->street;
            $city = $account->city;
            $state = $account->state;
            $zipcode = $account->zipcode;
            $country = $account->country;
        }
        $address = '';
        if ($street != '') {
            $address = $street . ', ';
        }
        if ($city != '') {
            $address .= $city . ', ';
        }
        if ($state != '') {
            $address .= $state . ' ';
        }
        if ($zipcode != '') {
            $address .= $zipcode;
        }
        if ($zipcode != '' OR $state != '') {
            $address .= ', ';
        }
        $address .= $country;
        return $address;
    }
}

if (! function_exists('outputTags')) {

    function outputTags($account, $labels = false)
    {
        if (!is_array($account) OR !count($account)) {
            return (!$labels) ? 'NONE' : "<span class='badge badge-danger'>NONE</span>";
        }
        $tags = Accounts::outputTags($account);
        if (!$labels) {
            return $tags;
        }
        $tagString = '';
        $tags = explode(',', $tags);
        foreach ($tags as $tag) {
            $tag = trim($tag);
            $tagString .= "<span class='badge badge-success'>{$tag}</span>\n";
        }
        return $tagString;
    }
}

if (! function_exists('getTagsOptions')) {
    function fixProduct($product='')
    {
        $fix = array(
            'Denave' => 'DenaVe',
            'Discovery Pico' => 'Discovery Pico',
            'Evo Series' => 'EVO Series',
            'Motus Ax/Ay' => 'Motus AX/AY',
            'Physiq' => 'Physiq',
            'Skinwave' => 'Skinwave',
            'Subnovii' => 'Subnovii',
            'Tetra Co2 And Coolpeel' => 'Tetra CO2 And Coolpeel',
            'V-Lase' => 'V-Lase',
            'Thunder' => 'Thunder',
            'V-Lase' => 'V-Lase',
            'Virtue RF' => 'VirtueRF',
            'AltaUV' => 'AltaUV',
            'Luxea' => 'Luxea',
            'Duetto' => 'Duetto MT EVO (Canada Only)',
            'Domino' => 'Domino EVO (Canada Only)',
            'YouLaser' => 'YouLaser MT (Canada Only)',
        );
        return (isset($fix[$product])) ? $fix[$product] : $product;
    }
}

if (! function_exists('getTagsOptions')) {
    function getTagsOptions()
    {
        $tags = Accounts::existingTags()->all();
        $result = array();
        foreach ($tags as $key => $tag) {
            $fixed = fixProduct($tag->name);
            $result[$tag->name] = $fixed;
            //$result .= "<option value=\"{$tag->name}\">{$fixed}</option>\n";
        }
        return $result;
    }
}

if (! function_exists('hasTag')) {

    function hasTag($accountTags, $theTag)
    {
        foreach ($accountTags as $test) {
            if (isset($test->name) && $test->name == $theTag) {
                return true;
            }
        }
        return false;
    }
}

if (! function_exists('getCountry')) {

    function getCountry()
    {
        $coutry = array(
            'United States' => 'United States',
            'Canada' => 'Canada',
        );
        return $coutry;
    }
}

if (! function_exists('getProvidences')) {

    function getProvidences()
    {
        $canadian_states = array(
            "AB" => "Alberta",
            "BC" => "Colombie-Britannique",
            "MB" => "Manitoba",
            "NB" => "Nouveau-Brunswick",
            "NL" => "Terre-Neuve-et-Labrador",
            "NS" => "Nouvelle-Écosse",
            "NT" => "Territoires du Nord-Ouest",
            "NU" => "Nunavut",
            "ON" => "Ontario",
            "PE" => "Île-du-Prince-Édouard",
            "QC" => "Québec",
            "SK" => "Saskatchewan",
            "YT" => "Yukon"
        );
        return $canadian_states;
    }
}
if (! function_exists('getStates')) {

    function getStates()
    {
        $states = array (
            'AL'=>'Alabama',
            'AK'=>'Alaska',
            'AZ'=>'Arizona',
            'AR'=>'Arkansas',
            'CA'=>'California',
            'CO'=>'Colorado',
            'CT'=>'Connecticut',
            'DE'=>'Delaware',
            'DC'=>'District Of Columbia',
            'FL'=>'Florida',
            'GA'=>'Georgia',
            'HI'=>'Hawaii',
            'ID'=>'Idaho',
            'IL'=>'Illinois',
            'IN'=>'Indiana',
            'IA'=>'Iowa',
            'KS'=>'Kansas',
            'KY'=>'Kentucky',
            'LA'=>'Louisiana',
            'ME'=>'Maine',
            'MD'=>'Maryland',
            'MA'=>'Massachusetts',
            'MI'=>'Michigan',
            'MN'=>'Minnesota',
            'MS'=>'Mississippi',
            'MO'=>'Missouri',
            'MT'=>'Montana',
            'NE'=>'Nebraska',
            'NV'=>'Nevada',
            'NH'=>'New Hampshire',
            'NJ'=>'New Jersey',
            'NM'=>'New Mexico',
            'NY'=>'New York',
            'NC'=>'North Carolina',
            'ND'=>'North Dakota',
            'OH'=>'Ohio',
            'OK'=>'Oklahoma',
            'OR'=>'Oregon',
            'PA'=>'Pennsylvania',
            'RI'=>'Rhode Island',
            'SC'=>'South Carolina',
            'SD'=>'South Dakota',
            'TN'=>'Tennessee',
            'TX'=>'Texas',
            'UT'=>'Utah',
            'VT'=>'Vermont',
            'VA'=>'Virginia',
            'WA'=>'Washington',
            'WV'=>'West Virginia',
            'WI'=>'Wisconsin',
            'WY'=>'Wyoming',
        );

        $canadian_states = array(
            "AB" => "Alberta",
            "BC" => "Colombie-Britannique",
            "MB" => "Manitoba",
            "NB" => "Nouveau-Brunswick",
            "NL" => "Terre-Neuve-et-Labrador",
            "NS" => "Nouvelle-Écosse",
            "NT" => "Territoires du Nord-Ouest",
            "NU" => "Nunavut",
            "ON" => "Ontario",
            "PE" => "Île-du-Prince-Édouard",
            "QC" => "Québec",
            "SK" => "Saskatchewan",
            "YT" => "Yukon"
        );
        $states = array('United States' => $states,'Canadian Providences' => $canadian_states);
        return $states;
    }
    function getStatesJSON()
    {
        $states = array (
            'AL'=>'Alabama',
            'AK'=>'Alaska',
            'AZ'=>'Arizona',
            'AR'=>'Arkansas',
            'CA'=>'California',
            'CO'=>'Colorado',
            'CT'=>'Connecticut',
            'DE'=>'Delaware',
            'DC'=>'District Of Columbia',
            'FL'=>'Florida',
            'GA'=>'Georgia',
            'HI'=>'Hawaii',
            'ID'=>'Idaho',
            'IL'=>'Illinois',
            'IN'=>'Indiana',
            'IA'=>'Iowa',
            'KS'=>'Kansas',
            'KY'=>'Kentucky',
            'LA'=>'Louisiana',
            'ME'=>'Maine',
            'MD'=>'Maryland',
            'MA'=>'Massachusetts',
            'MI'=>'Michigan',
            'MN'=>'Minnesota',
            'MS'=>'Mississippi',
            'MO'=>'Missouri',
            'MT'=>'Montana',
            'NE'=>'Nebraska',
            'NV'=>'Nevada',
            'NH'=>'New Hampshire',
            'NJ'=>'New Jersey',
            'NM'=>'New Mexico',
            'NY'=>'New York',
            'NC'=>'North Carolina',
            'ND'=>'North Dakota',
            'OH'=>'Ohio',
            'OK'=>'Oklahoma',
            'OR'=>'Oregon',
            'PA'=>'Pennsylvania',
            'RI'=>'Rhode Island',
            'SC'=>'South Carolina',
            'SD'=>'South Dakota',
            'TN'=>'Tennessee',
            'TX'=>'Texas',
            'UT'=>'Utah',
            'VT'=>'Vermont',
            'VA'=>'Virginia',
            'WA'=>'Washington',
            'WV'=>'West Virginia',
            'WI'=>'Wisconsin',
            'WY'=>'Wyoming',
        );

        $canadian_states = array(
            "AB" => "Alberta",
            "BC" => "Colombie-Britannique",
            "MB" => "Manitoba",
            "NB" => "Nouveau-Brunswick",
            "NL" => "Terre-Neuve-et-Labrador",
            "NS" => "Nouvelle-Écosse",
            "NT" => "Territoires du Nord-Ouest",
            "NU" => "Nunavut",
            "ON" => "Ontario",
            "PE" => "Île-du-Prince-Édouard",
            "QC" => "Québec",
            "SK" => "Saskatchewan",
            "YT" => "Yukon"
        );
        $states = array('United States' => $states,'Canadian Providences' => $canadian_states);
        echo 'var states = ' . json_encode($states, true) . ';' . PHP_EOL;
    }
}


if (! function_exists('static_map')) {
    function static_map($account) {
        $key = 'pk.396866a858c3dd693e18662e7a408d6c';
        $address = $account->formatted_address;
        if (!isset($account->formatted_address) OR $account->formatted_address == '') {
            $address = formatAddress($account->street, $account->city, $account->state, $account->zipcode, $account->country);
        }

        $geo = geocodeFree($address);
        if (!isset($geo->lat)) {
            return "https://placekitten.com/400/200";
        }
        $account->lat = $geo->lat;
        $account->lng = $geo->lng;
        /*
        if (env('APP_ENV') == 'production') {
            $key = env('GMAP_STATIC');
            $city = urlencode($account->city);
            $state = urlencode($account->state);
            $url = "https://maps.googleapis.com/maps/api/staticmap?center=$city,$state&zoom=13&size=400x200&maptype=roadmap
            &markers=color:red%7Clabel:C%7C{$account->lat},{$account->lng}&key={$key}";
            return $url;
        }
        */
        $url = "https://maps.locationiq.com/v2/staticmap?key={$key}&size=400x200&center={$account->lat},{$account->lng}&zoom=8&markers={$account->lat},{$account->lng}&format=png";
        ///$url = "https://static-maps.yandex.ru/1.x/?lang=en-US&ll=-{$account->lat},{$account->lng}&z=11&l=map,trf,skl&size=400,200&pt={$account->lat},{$account->lng},pmrdm1";
        return $url;
    }
}
if (! function_exists('geocode')) {

    // function to geocode address, it will return false if unable to geocode address
    function geocode($address)
    {
        $apikey = env('GMAP_API_KEY');
        $address = str_replace (" ", "+", urlencode($address));
        $details_url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&sensor=false&key={$apikey}";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $details_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = json_decode(curl_exec($ch), true);
        if ($response['status'] != 'OK') {
            $status = $response['status'];
            \Log::error("<strong>GMAP GEOCODE ERROR: {$status}</strong>");
            Bugsnag::notifyError('GMAP', 'GEOCACHE ERROR ' . $status, function ($report) use ($response) {
                $report->setSeverity('error');
                $report->setMetaData([
                    'response' => $response
                ]);
            });

            return false;
        }

        // response status will be 'OK', if able to geocode given address
        if($response['status']=='OK'){

            // get the important data
            $lat = isset($response['results'][0]['geometry']['location']['lat']) ? $response['results'][0]['geometry']['location']['lat'] : "";
            $lng = isset($response['results'][0]['geometry']['location']['lng']) ? $response['results'][0]['geometry']['location']['lng'] : "";
            $formatted_address = isset($response['results'][0]['formatted_address']) ? $response['results'][0]['formatted_address'] : "";

            // verify if data is complete
            if($lat && $lng && $formatted_address){

                $geocode = new \stdClass();

                $geocode->lat = $lat;
                $geocode->lng = $lng;
                $geocode->formatted_address = $formatted_address;
                return $geocode;
            }else{
                return false;
            }
        }
        if ($response['status'] != 'OK') {
            $status = $response['status'];
            \Log::error("<strong>GMAP GEOCACHE ERROR: {$status}</strong>");
            Bugsnag::notifyError('GMAP', 'GEOCACHE ERROR ' . $status, function ($report) use ($response) {
                $report->setSeverity('error');
                $report->setMetaData([
                    'response' => $response
                ]);
            });

            return false;
        }

        // response status will be 'OK', if able to geocode given address
        if($response['status']=='OK'){

            // get the important data
            $lat = isset($response['results'][0]['geometry']['location']['lat']) ? $response['results'][0]['geometry']['location']['lat'] : "";
            $lng = isset($response['results'][0]['geometry']['location']['lng']) ? $response['results'][0]['geometry']['location']['lng'] : "";
            $formatted_address = isset($response['results'][0]['formatted_address']) ? $response['results'][0]['formatted_address'] : "";

            // verify if data is complete
            if($lat && $lng && $formatted_address){

                $geocode = new \stdClass();

                $geocode->lat = $lat;
                $geocode->lng = $lng;
                $geocode->formatted_address = $formatted_address;
                return $geocode;
            }else{
                return false;
            }
        }
    }

}

if (! function_exists('geocodeFree')) {

    // function to geocode address, it will return false if unable to geocode address
    function geocodeFree($address)
    {
        if (env('APP_ENV') == 'production') {
            $geo = geocode($address);
            if (isset($geo) OR is_object($geo) OR isset($geo->formatted_address)) {
               return geocode($address);
            }
        }
        $apikey = 'pk.396866a858c3dd693e18662e7a408d6c';
        //env('LOCATIONIQ_API_KEY');
        $address = str_replace (" ", "+", urlencode($address));
        $details_url = "https://us1.locationiq.org/v1/search.php?q={$address}&&format=json&key={$apikey}&normalizecity";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $details_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = json_decode(curl_exec($ch), true);

        if (is_array($response) && isset($response[0]) && is_array($response[0]) && isset($response[0]['lat'])) {
            // get the important data
            $result = $response[0];
            $lat = isset($result['lat']) ? $result['lat'] : "";
            $lng = isset($result['lon']) ? $result['lon'] : "";
            $formatted_address = isset($result['display_name']) ? $result['display_name'] : "";
            $geocode = new \stdClass();

            $geocode->lat = $lat;
            $geocode->lng = $lng;
            $geocode->formatted_address = $formatted_address;
            return $geocode;
        }

        \Log::error("<strong>LOCATION IQ ERROR</strong>");
        Bugsnag::notifyError('LOCATIONIQ', 'GEOCACHE ERROR ' . $status, function ($report) use ($response) {
            $report->setSeverity('error');
            $report->setMetaData([
                'response' => $response
            ]);
        });

        return false;
    }

}
