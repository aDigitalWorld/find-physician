<?php
namespace App\Http\Controllers\Frontend\Api;

use App\Http\Controllers\Controller;
use App\Models\Accounts;
use DB;
use Illuminate\Http\Request;
use zcrmsdk\oauth\ZohoOAuth;
use Illuminate\Support\Arr;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use RuntimeException;
use Illuminate\Pagination\Paginator;
/**
 * Class DashboardController.
 */
class ApiController extends Controller
{
    protected $limit = 25;
    public function fixProduct($product='')
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
        );
        return (isset($fix[$product])) ? $fix[$product] : $product;
    }
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function tags(Request $request)
    {
        $debug = $request->get('debug', false);
        $tags = Accounts::existingTags()->all();
        if ($debug) {
            dump($tags);die;
        }
        $formatted_tags = [];
        foreach ($tags as $tag) {
            $tag->name = $this->fixProduct($tag->name);
            $formatted_tags[] = ['id' => $tag->slug, 'text' => $tag->name];
        }
        return response()->json($formatted_tags)->setStatusCode(200)->header('Content-Type', 'application/json');
    }
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function all(Request $request)
    {
        $tag  = $request->get('tag', false);
        $page = (int) $request->get('page', 1);
        $num  = $page * 5;
        if ($tag) {
            $tagSearch = array($tag);
            if ($tag == 'rf-microneedling') {
                $tagSearch = array('rf-microneedling', 'virtue-rf');
            }
            $accounts = Accounts::complete()->withAnyTag($tagSearch)->get();
        } else {
            $accounts = Accounts::complete()->with('tagged')->get();
        }
        foreach ($accounts as $row) {
            $row->tagged = $row->tagged;
        }

        $accounts = $accounts->values();
        return response()->json($accounts)->setStatusCode(200)->header('Content-Type', 'application/json');
    }
    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $longitude = $request->get('lng', '-78.8948741');
        $latitude  = $request->get('lat', '33.7070405');
        $radius    = $request->get('radius', 50);
        $tag       = $request->get('tag', false);
        $radius = !is_numeric($radius) ? 50 : $radius;
         \DB::enableQueryLog();
         $result = Accounts::haversine(new Accounts(), $latitude, $longitude, $radius, 'miles', $tag,array('accounts.*'));
         foreach ($result as $row) {
             $row->tagged = $row->tagged;
         }
         if (!$tag) {
            $result = $result->values();
            return response()->json($result)->setStatusCode(200)->header('Content-Type', 'application/json');
        }
        $filtered = $result->filter(function ($value, $key) use($tag) {
            foreach($value->tagged as $row) {
                if ($tag == 'rf-microneedling' && ($row->tag_slug == 'rf-microneedling' || $row->tag_slug == 'virtue-rf')) {
                    return true;
                }
                if ($row->tag_slug == $tag) {
                    return true;
                }
            }
            return false;
        });

        $filtered = $filtered->values();
        return response()->json($filtered)->setStatusCode(200)->header('Content-Type', 'application/json');
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function grantToken(Request $request)
    {
        $oAuthClient = ZohoOAuth::getClientInstance();
        $grantToken  = $request->get('code', NULL);
        return $oAuthTokens = $oAuthClient->generateAccessToken($grantToken);
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function refreshToken(Request $request)
    {
        $oAuthClient    = ZohoOAuth::getClientInstance();
        $refreshToken   = $request->get('code', NULL);
        $userIdentifier = "brandon@southerntidemedia.com";
        return $oAuthTokens = $oAuthClient->generateAccessTokenFromRefreshToken($refreshToken,$userIdentifier);
    }

    public function download(Request $request)
    {
        $auth = $request->get('auth', false);

        if (!$auth OR $auth != 'erin gobragh') die('no access');

        $csv = 'Account, Device Name, Training Date, Street, City, State, Zipcode, Country, Lat, Lng' . PHP_EOL;
        $csv = 'Account, Device Name, Training Date, Formatted Address, Street, City, State, Zipcode, Country, Lat, Lng' . PHP_EOL;

        $accounts = Accounts::complete()->with('tagged')->get()->toArray();
//        $accounts = Accounts::complete()->with('tagged')->get();
        foreach($accounts as $account) {
            foreach ($account['tagged'] as $tagged) {
                $account['street'] = is_null($account['street']) ? '' : $account['street'];
                $account['street'] = str_replace(',', '', $account['street']);
                $account['zipcode'] = is_null($account['zipcode']) ? '' : $account['zipcode'];
                $account['state'] = is_null($account['state']) ? '' : $account['state'];
                $account['city']  = is_null($account['city']) ? '' : $account['city'];
                $account['country'] = is_null($account['country']) ? '' : $account['country'];
                $account['name'] = str_replace(',', '', $account['name']);
                if (strlen($account['zipcode']) == 4) {
                    $account['zipcode'] = '0'.$account['zipcode'];
                }

                $device = $this->fixProduct($tagged['tag_name']);
                $csv .= vsprintf('"%s","%s","%s","%s","%s","%s","%s","%s","%s","%s","%s"', array(
                    $account['name'], $device, $account['training_date'], $account['formatted_address'], $account['street'],
                    $account['city'], $account['state'], $account['zipcode'], $account['country'], $account['lat'],
                    $account['lng']
                )) . PHP_EOL;
//                $csv .= '"'.$account['name'] . '", ' . $device . ', ' . $account['training_date'] . ', ' . $account['street'] . ', ' . $account['city'] . ', ' . $account['state'] . ', ' . $account['zipcode'] . ', ' . $account['country'] . ', ' . $account['lat'] . ', ' . $account['lng'] . PHP_EOL;
            }
        }
        return response($csv)->setStatusCode(200)->header('Content-Type', 'text/x-csv')->header('Content-Disposition', 'attachment; filename="accounts.csv"');
    }

}
