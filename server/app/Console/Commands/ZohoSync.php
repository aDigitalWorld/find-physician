<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use IlluminateSupportFacadesLog;
use App\Models\Accounts;
use zcrmsdk\crm\crud\ZCRMModule;
use zcrmsdk\crm\setup\org\ZCRMOrganization;
use zcrmsdk\crm\setup\restclient\ZCRMRestClient;
use zcrmsdk\crm\setup\users\ZCRMProfile;
use zcrmsdk\crm\setup\users\ZCRMRole;
use zcrmsdk\crm\setup\users\ZCRMUser;
use zcrmsdk\crm\crud\ZCRMOrgTax;
use zcrmsdk\oauth\ZohoOAuth;
use League\CLImate\CLImate;
use Spatie\Tags\Tag;
use \Mailjet\Resources;
use Mailjet\LaravelMailjet\Facades\Mailjet;

class ZohoSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature   = 'zoho:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update database with leads from ZohoCRM';

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

    public function process_data($account, $data)
    {
        $zipcodeRegex = '^\d{5}(?:[-\s]\d{4})?$^';
        $street  = $data['Shipping_Street'] ?? $data['Billing_Street'];
        $city    = $data['Shipping_City'] ?? $data['Billing_City'];
        $state   = $data['Shipping_State'] ?? $data['Billing_State'];
        $country = $data['Shipping_Country'] ?? $data['Billing_Country'];
        $country = str_replace('.','',$country);
        if ( preg_match($zipcodeRegex, $data['Shipping_Code'])) {
            $zipcode = $data['Shipping_Code'];
        } elseif ( preg_match($zipcodeRegex, $data['Billing_Code'])) {
            $zipcode = $data['Billing_Code'];
        } else {
            $zipcode = '';
        }
        $address = $street . ',' . $city . ' ' . $zipcode . ' ' . $country;
        $geocode = $this->geocode($address);

        $state = self::format_state($state, 'abbr');
        $account->name = $data['Account_Name'];
        $account->street = is_null($street) ? '' : $street;
        $account->zipcode = is_null($zipcode) ? '' : $zipcode;
        $account->state = is_null($state) ? '' : $state;
        $account->city  = is_null($city) ? '' : $city;
        $account->country = is_null($country) ? '' : $country;
        $account->website = is_null($data['Website']) ? '' : $data['Website'];
        $account->lat  = isset($geocode->lat) ? $geocode->lat : '';
        $account->lng  = isset($geocode->lng) ? $geocode->lng : '';
        $account->formatted_address = isset($geocode->formatted_address) ? $geocode->formatted_address : '';
        $account->created_at = $data['created_at'];
        $account->modified_at = $data['modified_at'];
        $account->save();
    }

    public function fetch_records()
    {
        # code...
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
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
        $moreRecords = true;
        $page = 1;
        $updates = $skipped = $new = 0;
        $climate = new \League\CLImate\CLImate;
        $climate->lightGreen()->out('Syncing database with ZohoCRM API.....');
        $climate->lightGreen()->border('*', 40);
        $module = ZCRMModule::getInstance("Accounts");
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
                $progress = $climate->progress()->total($numRecords);
            } catch (ZCRMException $e) {
                echo $e->getCode();
                echo $e->getMessage();
                echo $e->getExceptionCode();
            }

            foreach ($recordsArray as $record) {
                $data = $record->getData();
                $progress->advance(1, 'Page Number: '.($page - 1));
                $data['created_at'] = $record->getCreatedTime();
                $data['modified_at'] = $record->getModifiedTime();
                $data['created_at']  = strtotime($data['created_at']);
                $data['modified_at'] = strtotime($data['modified_at']);
                $data['created_at']  = date("Y-m-d H:i:s", $data['created_at']);
                $data['modified_at'] = date("Y-m-d H:i:s", $data['modified_at']);

                $account = Accounts::where('name', $data['Account_Name'])->first();
                if (isset($account->modified_at)) {
                    if ($account->modified_at == $data['modified_at']) {
                        $skipped++;
                        continue;
                    }
                    $updates++;
                } else {
                    $account = new Accounts;
                    $new++;
                }
                $this->process_data($account,$data);
            }

        }

        $padding = $climate->padding(10);
        $padding->label('Created')->result($new);
        $padding->label('Updated')->result($updates);
        $padding->label('Skipped')->result($skipped);

        $time_elapsed_secs = microtime(true) - $start;
        $time_elapsed_secs = $this->elapsed($time_elapsed_secs);
        $padding->label('Time Completed in')->result($time_elapsed_secs);
        $subject = 'ZohoSync completed at Cartessa';
        $message = "
        <h2>Syncing data complete</h2><p>
        Created {$new}<br>
        Updated {$updates}<br>
        Skipped {$skipped}<br></p>
        <p>Completed in {$time_elapsed_secs}</p>";
        $headers = 'From: maps@cartessa.com' . "\r\n" .
                   'Reply-To: ithippyshawn@gmail.com' . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();
        mail('ithippyshawn@gmail.com', $subject, $message, $headers);
        // use the below scope
        //ZohoCRM.modules.ALL,ZohoCRM.settings.ALL,aaaserver.profile.READ,ZohoCRM.org.all,ZohoCRM.users.all,ZohoCRM.bulk.all,ZohoCRM.modules.accounts.all

        $mj = new \Mailjet\Client(getenv('MAILJET_APIKEY'), getenv('MAILJET_APISECRET'),true,['version' => 'v3.1']);
        $body = [
            'Messages' => [
                [
                    'From' => [
                        'Email' => "ithippyshawn@gmail.com",
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
/*
        $mailjet = Mailjet::getClient();
        // Resources are all located in the Resources class
        $response = $mailjet->get(Resources::$Contact);

        if ($response->success()) {
            var_dump($response->getData());
        } else {
            var_dump($response->getStatus());
        }
        $body = array(
            'FromEmail' => "ithippyshawn@gmail.com",
            'FromName' => "ZohoCRM CRON Report",
            'Subject' => $subject,
            'Text-part' => strip_tags($message),
            'Html-part' => $message,
            'Recipients' => [['Email' => "ithippyshawn@gmail.com"]]
        );
        $response = $mailjet->post(Resources::$Email, ['body' => $body]);
*/
    }

    public function elapsed($seconds) {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        $seconds = $seconds % 60;
        $hours = ($hours > 0) ? $hours . ' hours' : '';
        $minutes = ($minutes > 0) ? $minutes . ' minutes' : '';
        $seconds = ($seconds > 0) ? $seconds . ' seconds' : '';

        return "$hours $minutes $seconds";
    }
}
