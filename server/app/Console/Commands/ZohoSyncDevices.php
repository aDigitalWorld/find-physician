<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use IlluminateSupportFacadesLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use InvalidArgumentException;
use App\Models\Accounts;
use zcrmsdk\crm\crud\ZCRMModule;
use zcrmsdk\crm\crud\ZCRMRecord;
use zcrmsdk\crm\setup\org\ZCRMOrganization;
use zcrmsdk\crm\setup\restclient\ZCRMRestClient;
use zcrmsdk\crm\setup\users\ZCRMProfile;
use zcrmsdk\crm\setup\users\ZCRMRole;
use zcrmsdk\crm\setup\users\ZCRMUser;
use zcrmsdk\crm\crud\ZCRMOrgTax;
use zcrmsdk\oauth\ZohoOAuth;
use League\CLImate\CLImate;
use \Mailjet\Resources;
use Mailjet\LaravelMailjet\Facades\Mailjet;
use Carbon\Carbon;

class ZohoSyncDevices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature   = 'zoho:syncDevices';
    protected $tags   = array();

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all tags';
    protected $oldProducts = array('DenaVe 585nm System', 'Discovery Pico Plus', 'Discovery Pico Plus with Fractional HP', 'Evo Light 4V', 'Evo Light A Star', 'Evo Q Plus C', 'Luxea', 'Luxea with Virdis HP', 'Motus AX', 'Motus AY', 'Physiq', 'Skinwave', 'Subnovii', 'Tetra CO2: 30W', 'V-Lase', 'Virtue RF', 'Vivace RF Microneedling', 'Tetra CO2: 50W', 'Thunder', 'AltaUV', 'SLIM E30 MIXTO SX TFT with V-LASE', 'Studio');
    protected $products = array('DenaVe', 'Discovery Pico', 'EVO series', 'Luxea', 'AltaUV', 'Motus AX/AY', 'Physiq', 'V-Lace', 'Skinwave', 'Subnovii', 'Tetra CO2 and Coolpeel', 'V-Lase', 'Virtue RF', 'VirtueRF', 'Thunder', 'Vivace RF Microneedling', 'RF Microneedling', 'Duetto MT EVO', 'Domino EVO', 'YouLaser MT');
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Format State
     *
     * Note: Does not format addresses, only states. $input should be as exact as possible, problems
     * will probably arise in long strings, example 'I live in Kentukcy' will produce Indiana.
     *
     * @example echo myClass::format_state( 'Florida', 'abbr'); // FL
     * @example echo myClass::format_state( 'we\'re from georgia' ) // Georgia
     *
     * @param  string $input  Input to be formatted
     * @param  string $format Accepts 'abbr' to output abbreviated state, default full state name.
     * @return string          Formatted state on success,
     */
    static function format_state( $input, $format = '' ) {
        if( ! $input || empty( $input ) )
            return;

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

        $states = array_merge($states, $canadian_states);
        foreach( $states as $abbr => $name ) {
            if ( preg_match( "/\b($name)\b/", ucwords( strtolower( $input ) ), $match ) )  {
                if( 'abbr' == $format ){
                    return $abbr;
                }
                else return $name;
            }
            elseif( preg_match("/\b($abbr)\b/", strtoupper( $input ), $match) ) {
                if( 'abbr' == $format ){
                    return $abbr;
                }
                else return $name;
            }
        }
        return;
    }

    // function to geocode address, it will return false if unable to geocode address
    protected function geocode($address)
    {
        $apikey = env('GMAP_API_KEY');
        $address = urlencode($address);
        $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$address}&key={$apikey}";
        $resp_json = file_get_contents($url);
        $resp = json_decode($resp_json, true);
        // response status will be 'OK', if able to geocode given address
        if($resp['status']=='OK'){

            // get the important data
            $lat = isset($resp['results'][0]['geometry']['location']['lat']) ? $resp['results'][0]['geometry']['location']['lat'] : "";
            $lng = isset($resp['results'][0]['geometry']['location']['lng']) ? $resp['results'][0]['geometry']['location']['lng'] : "";
            $formatted_address = isset($resp['results'][0]['formatted_address']) ? $resp['results'][0]['formatted_address'] : "";

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

        else{
            \Log::error("<strong>GMAP GEOCODE ERROR: {$resp['status']}</strong>");
            return false;
        }
    }
    public function _fix_product_name($tag)
    {
        $check = strtolower($tag);

        if (strpos($check, 'duetto') !== false OR $check == 'duetto mt evo') {
            return 'Duetto MT EVO';
        }
        if (strpos($check, 'youlaser') !== false OR $check == 'youlaser mt') {
            return 'YouLaser MT';
        }

        if (strpos($check, 'youlaser') !== false OR $check == 'youlaser mt') {
            return 'Youlaser MT';
        }

        if (strpos($check, 'denave') !== false) {
            return 'DenaVe';
        }
        if (strpos($check, 'discovery pico') !== false) {
            return 'Discovery Pico';
        }
        if (strpos($check, 'luxea') !== false) {
            return 'Luxea';
        }
        if (strpos($check, 'evo') !== false) {
            return 'EVO series';
        }
        if (strpos($check, 'luxea') !== false OR $check == 'luxea with virdis hp') {
            return 'Luxea';
        }
        if (strpos($check, 'tetra') !== false OR $check == 'tetra co2: 30w') {
            return 'Tetra CO2 and Coolpeel';
        }
        if (strpos($check, 'altauv') !== false OR $check == 'altauv') {
            return 'AltaUV';
        }
        if (strpos($check, 'motus') !== false) {
            return 'Motus AX/AY';
        }
        if ($check == 'physiq' OR strpos($check, 'physiq') !== false) {
            return 'Physiq';
        }
        if (strpos($check, 'skinwave') !== false) {
            return 'Skinwave';
        }
        if (strpos($check, 'subnovii') !== false) {
            return 'Subnovii';
        }
        if (strpos($check, 'slim e30 mixto sx tft with v-lase') !== false) {
            return 'V-Lase';
        }
        if (strpos($check, 'studio') !== false OR strpos($check, 'v-lase') !== false) {
            return 'V-Lase';
        }
        if (strpos($check, 'virtue rf') !== false OR $check == 'virtue rf') {
            return 'Virtue RF';
        }
        if (strpos($check, 'Vivace RF Microneedling') !== false OR $check == 'Vivace RF Microneedling') {
            return 'VirtueRF and Vivace Customers';
        }
        if (strpos($check, 'virtuerf') !== false OR $check == 'virtuerf') {
            return 'Virtue RF';
        }
        if (strpos($check, 'vivace rf microneedling') !== false OR $check == 'vivace rf microneedling') {
            return 'RF Microneedling';
        }
        if (strpos($check, 'rf microneedling') !== false OR $check == 'rf microneedling') {
            return 'RF Microneedling';
        }
        return $tag;
    }
    public function formatAddress($street, $city, $state, $zipcode, $country)
    {
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
    public function process_data($account, $data)
    {
        if (isset($account->override) && $account->override == 1) return $account;
        $zipcodeRegex = '^\d{5}(?:[-\s]\d{4})?$^';
        if (empty($data['Shipping_City']) && empty($data['Shipping_State'])) {
            $street  = isset($data['Billing_Street']) ? $data['Billing_Street'] : '';
            $city    = isset($data['Billing_City']) ? $data['Billing_City'] : '';
            $state   = isset($data['Billing_State']) ? $data['Billing_State'] : '';
            $country = isset($data['Billing_Country']) ? $data['Billing_Country'] : '';
            $zipcode = $data['Billing_Code'];
        } else {
            $street  = isset($data['Shipping_Street']) ? $data['Shipping_Street'] : '';
            $city    = isset($data['Shipping_City']) ? $data['Shipping_City'] : '';
            $state   = isset($data['Shipping_State']) ? $data['Shipping_State'] : '';
            $country = isset($data['Shipping_Country']) ? $data['Shipping_Country'] : '';
            $zipcode = $data['Shipping_Code'];
        }

        $country = str_replace('.','',$country);
        if ($country == '' OR strtolower($country) == 'us' OR strtolower($country) == 'usa') {
            $country = 'United States';
        }
        if (strlen($zipcode) > 5) {
            $zipcode = substr($zipcode, 0, 5);
        }

        if ($data['Website'] != '' && (!(substr($data['Website'], 0, 7) == 'http://')) && (!(substr($data['Website'], 0, 8) == 'https://'))) {
            $data['Website'] = 'https://' . $data['Website'];
        }
        $address = $street . ',' . $city . ' ' . $zipcode . ' ' . $country;

        if (!isset($account->lat) OR empty($account->lat) OR !isset($account->lng) OR empty($account->lng)) {
            $geocode = geocode($address);

            $account->lat  = isset($geocode->lat) ? $geocode->lat : '';
            $account->lng  = isset($geocode->lng) ? $geocode->lng : '';
            $account->formatted_address = isset($geocode->formatted_address) ? $geocode->formatted_address : '';
        }

        $state = self::format_state($state, 'abbr');
        $account->name = $data['Account_Name'];
        $account->street = is_null($street) ? $account->street : $street;
        $account->zipcode = is_null($zipcode) ? $account->zipcode : $zipcode;
        $account->state = is_null($state) ? $account->state : $state;
        $account->city  = is_null($city) ? $account->city : $city;
        $account->country = is_null($country) ? $account->country : $country;
        $account->website = is_null($data['Website']) ? $account->website : $data['Website'];

        // $account->ZohoID = $data['ZohoID'];
        $account->ZohoID = isset($data['ZohoID']) ? $data['ZohoID'] : $account->ZohoID;
        $account->delivery_date = isset($data['delivery_date']) ? $data['delivery_date'] : $account->delivery_date;
        $account->install_date = isset($data['install_date']) ? $data['install_date'] : $account->install_date;

        $account->phone = isset($data['Phone']) ? $data['Phone'] : $account->phone;
        $account->training_date = isset($data['training_date']) ? $data['training_date'] : $account->training_date;
        $account->formatted_address = isset($geocode->formatted_address) ? $geocode->formatted_address : $account->formatted_address;
        if (!isset($account->formatted_address) OR empty($account->formatted_address)) {
            $account->formatted_address = $this->formatAddress($street, $city, $state, $zipcode, $country);
        }

        if (!isset($account->zoho_sync_date) OR empty($account->zoho_sync_date)) {
            $account->zoho_sync_date = Carbon::now();
        }
        $account->zoho_sync_date = $account->zoho_sync_date;
        $account->zoho_created_at = $data['created_at'];
        $account->zoho_modified_at = $data['modified_at'];
        $account->active = isset($account->active) ? $account->active : 1;
        $account->override = isset($account->override) ? $account->override : 0;
        $account->save();
        return $account;
    }
    public function handle()
    {
        $start = microtime(true);
        $configuration = array(
            'client_id' => '1000.8XYKIJOSBT6L90523EUFF2APBJVD4H',
            'client_secret' => '4e89274db40d38a1980aad256620d8bf3849993635',
            'redirect_uri' => 'https://cartessaaesthetics.com/oauthcallback',
            'applicationLogFilePath' => storage_path('zoho'),
            'token_persistence_path' => storage_path('zoho/tokens'),
            'sandbox' => true,
            'currentUserEmail' => 'brandon@southerntidemedia.com',
        );
//        $zohoAuthToken = '1000.73724133e5c049b47466256141023b1c.8f78018effeecff2ef1412f069c4acf2';
        $zohoClient     = ZCRMRestClient::initialize($configuration);
        $oAuthClient = ZohoOAuth::getClientInstance();
        $refreshToken = "1000.822dbb644d515127cc9fcef8b6930685.b0bcd0945e51aa680a71642380d08a73";
        $userIdentifier = "brandon@southerntidemedia.com";
        $oAuthTokens = $oAuthClient->generateAccessTokenFromRefreshToken($refreshToken,$userIdentifier);

        //$climate = new \League\CLImate\CLImate;

        $moreRecords = true;
        $page = 1;
        $accountsUpdated = $newAccounts = 0;
        $fails = $updates = $skipped = $new = 0;
        $missingDate = 0;
        $errors = '';

        $this->info('Fetching devices from ZohoCRM API.....');
        $border = '*';
        for ($i=0; $i < 41; $i++) {
            $border .= "*";
        }
        $border .= "*\n";
        $this->info($border);
        $module = ZCRMModule::getInstance("Products_Sold");
        $newAccountAdded = $recordsArray = array();
        $badProducts = array();
        $badAccounts = array();
        $duplicates = 0;
        $totalRecords = 0;
        while ($moreRecords != false) {
            try{
                $params = array("page"=>$page, "per_page"=>200);
                $headers = array('junk'=>false);
                $bulkAPIResponse = $module->getRecords($params, $headers, null, $page, 200, null);
                $recordsArray    = $bulkAPIResponse->getData();
                $info            = $bulkAPIResponse->getInfo();
                $moreRecords     = $info->getMoreRecords();
                $page            = $info->getPageNo();
                $numRecords      = $info->getRecordCount();
                $page++;
                //$progress = $climate->progress()->total($numRecords);
            } catch (ZCRMException $e) {
                Bugsnag::notifyException($e, function ($report) {
                    $report->setSeverity('error');
                });
            }
            // $bar = $this->output->createProgressBar($totalRecords);
            foreach ($recordsArray as $record) {
                // $bar->advance();
                $data = $record->getData();
                if (!isset($data['Customer']) OR !isset($data['Product'])) {
                    continue;
                }

                if (!isset($data['Training_Date']) OR empty($data['Training_Date']) OR is_null($data['Training_Date'])) {
                    $missingDate++;
                    continue;
                }

                try{
                    $CustomerID   = $data['Customer']->getEntityId();
                    $DeviceID     = $data['Product']->getEntityId();
                    $trainingDate = !empty($data['Training_Date']) ? $data['Training_Date'] : '1969-01-01';
                    $deliveryDate = !empty($data['Delivery_Date']) ? $data['Delivery_Date'] : '1969-01-01';
                    $installDate  = !empty($data['Install_Date']) ? $data['Install_Date'] : '1969-01-01';
                    $apiResponse = ZCRMModule::getInstance('Accounts')->getRecord($CustomerID);
                    $accountData = $apiResponse->getData();
                    $name        = $accountData->getFieldValue("Account_Name");
                    if (strtolower($name) == 'cartessa aesthetics') continue;
// if (strtolower($name) != 'trouvaille medspa') continue;
                    $apiResponse = ZCRMModule::getInstance('Products')->getRecord($DeviceID);
                    $productData = $apiResponse->getData();
                    $org         = $productData->getFieldValue("Product_Name");

// dump($data, $accountData, $CustomerID, $DeviceID);
// die;
                    $this->info('Product ' . $org . ' Customer ' . $name . ' ZohoID:' . $CustomerID);
                    $tag         = $this->_fix_product_name($org);
                    if (!in_array($tag, $this->products)) {
                        $this->error('Skipping product ' . $tag);
                        $badProducts[] = $tag;
                        $badAccounts[] = $name;
                        continue;
                    }

                    $account = Accounts::where('ZohoID', $CustomerID)->first();
                    $theData = $accountData->getData();
                    $theData['ZohoID'] = $CustomerID;
                    $theData['training_date'] = date('Y-m-d', strtotime($trainingDate));
                    $theData['delivery_date'] = date('Y-m-d', strtotime($deliveryDate));
                    $theData['install_date']  = date('Y-m-d', strtotime($installDate));
                    $theData['created_at'] = date('Y-m-d H:i:s', strtotime($accountData->getCreatedTime()));
                    $theData['modified_at'] = date('Y-m-d H:i:s', strtotime($accountData->getModifiedTime()));
                    if (isset($account->modified_at)) {
                        $this->process_data($account,$theData);
                        $accountsUpdated++;
                    } else {
                        $account = new Accounts;
                        $this->process_data($account,$theData);
                        $newAccountAdded[$CustomerID][] = $name;
                        $newAccounts++;
                    }
                    $account->untag();
                    if (!isset($this->tags[$CustomerID])) {
                        $this->tags[$CustomerID] = array();
                    }
                    if (isset($this->tags[$CustomerID]) && !in_array($tag, $this->tags[$CustomerID])) {
                        $this->tags[$CustomerID][] = $tag;
                        $this->info("Customer Account:{$name} with Tag:{$tag}");
                    } else {
                        $duplicates++;
                    }
                } catch (ZCRMException $e) {
                    Bugsnag::notifyException($e, function ($report) use ($CustomerID, $theData) {
                        $report->setSeverity('error');
                        $report->setMetaData([
                            'ZohoData' => array(
                                'CustomerID' => $CustomerID,
                                'theData' => $theData,
                            )
                        ]);
                    });
                }

            }
        }
        $products_found = array();
        foreach ($this->tags as $key => $value) {
            $account = Accounts::where('ZohoID', $key)->first();
            if ($account && count($value) > 0) {
                $tags = array_unique($value);
                foreach ($tags as &$tag) {
                    if ($tag == null || $tag == '') continue;
                    $products_found[] = $tag;
                    $this->info("Tagging Account:{$key} with Tag:{$tag}");
                    try {
                        $account->tag([$tags]);
                        $account->save();
                        $updates++;
                    } catch (Exception $e) {
                        $fails++;
                        $errors .= '<hr>ERROR:' . $account->id . ') ' . $e->getMessage . '<br>';
                        $errors .= 'TAGS: ' . $tags . '<br><hr>';
                        Bugsnag::notifyException($e, function ($report) use ($CustomerID, $account, $key, $tag) {
                            $report->setSeverity('error');
                            $report->setMetaData([
                                'ZohoData' => array(
                                    'CustomerID' => $CustomerID,
                                    'key' => $key,
                                    'tag' => $tag,
                                    'account' => $account,
                                )
                            ]);
                        });

                    }
                }
            }

        }
        // $bar->finish();
        //$padding = $climate->padding(10);
        //$accountHeaders = ['Updated Accounts', 'New Accounts', 'Missing Training Date', 'Tags Updated', 'Fails'];
        //$accountResults = [$accountsUpdated, $newAccounts, $missingDate, $updates, $fails];
        //$this->table($accountHeaders, $accountResults);
        //$padding->label('Updated')->result($updates);
        $time_elapsed_secs = microtime(true) - $start;
        $time_elapsed_secs = $this->elapsed($time_elapsed_secs);
        $this->info('Time Completed in ' . $time_elapsed_secs);
/*
//        $products_found = (string) $this->fixArray($products_found);
        $badProducts    = (string) $this->fixArray($badProducts);
        $badAccounts    = (string) $this->fixArray($badAccounts);
        $newAccountAdded = (string) $this->fixArray($newAccountAdded);
*/
        $products_found = $badProducts = $badAccounts = $newAccountAdded = 'disabled';

        $subject = 'ZohoSyncDevices completed at Cartessa';
        $message = "
        <h2>Syncing data complete</h2><p>
        New Accounts Created {$newAccounts}<br>
        Accounts Updated {$accountsUpdated}<br>
        Customer Duplicates {$duplicates}<br>
        Missing Date {$missingDate}<br>
        Tags Updated {$updates}<br><br>";
        /*
        New Accounts Created: {$newAccountAdded}<br>
        Bad Products Not Tagged: {$badProducts}<br>
        Bad Accounts Not Tagged: {$badAccounts}<br>";
        */
        if ($errors ) {
            $message .= $errors;
        }
        $message .= "<p>Completed in {$time_elapsed_secs}.</p>";
        $headers = 'From: maps@cartessaaesthetics.com' . "\r\n" .
                   'Reply-To: ithippyshawn@gmail.com' . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();
        $mj = new \Mailjet\Client(getenv('MAILJET_APIKEY'), getenv('MAILJET_APISECRET'),true,['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "maps@cartessaaesthetics.com",
                        'Name' => "ZohoCRM CRON Report"
                    ],
                    'To' => [
                        [
                            'Email' => "ithippyshawn@gmail.com",
                            'Name' => "Shawn Crigger"
                        ]
                    ],
                    'Subject' => $subject,
                    'TextPart' => strip_tags($message),
                    'HTMLPart' => $message,
                ]
            ]
        ];
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        $response->success() && var_dump($response->getData());
    }

    function fixArray($myArray) {
        if(!is_array($myArray))  return $myArray;
        try {

            foreach ($myArray as &$myvalue) {
                $myvalue = serialize($myvalue);
            }

            $myArray = array_unique($myArray);

            foreach ($myArray as &$myvalue){
                $myvalue = unserialize($myvalue);
            }
        } catch (Exception $e) {
            Bugsnag::notifyException($e, function ($report) use ($myArray) {
                $report->setSeverity('error');
                $report->setMetaData([
                    '$myArray' => $myArray
                ]);
            });
            return 'ERROR: ' . $e->getMessage();
        }

        return $myArray;
    }
    public function elapsed($seconds) {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        $seconds = $seconds % 60;
        $hours = ($hours > 0) ? $hours . ' hours' : '';
        $minutes = ($minutes > 0) ? $minutes . ' minutes' : '';
        $seconds = ($seconds > 0) ? $seconds . ' seconds' : '';

        return "{$hours} {$minutes} {$seconds}";
    }
}
