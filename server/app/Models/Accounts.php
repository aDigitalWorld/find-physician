<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
/**
 * Class Accounts.
 */
class Accounts extends \Illuminate\Database\Eloquent\Model
{
    use \Conner\Tagging\Taggable;
    public $timestamps = false;
    protected $fillable = ["name", "street", "city", "state", "zipcode", "country", "website", "lat", "lng", "training_date", "phone", "created_at", "modified_at", "formatted_address", "tags", "created_at", "modified_at"];

    public function scopeComplete($query)
    {
        return $query->where('training_date', '!=', '');
    }

    /*
     *  find the n closest locations
     *  @param Model $query eloquent model
     *  @param float $lat latitude of the point of interest
     *  @param float $lng longitude of the point of interest
     *  @param float $max_distance distance in miles or km
     *  @param string $units miles or kilometers
     *  @param Array $fiels to return
     *  @return array
     */
     public static function haversine($query, $lat, $lng, $max_distance = 25, $units = 'miles', $tag = false, $fields = false )
     {

        if(empty($lat)){
            $lat = 0;
        }

        if(empty($lng)){
            $lng = 0;
        }

        /*
         *  Allow for changing of units of measurement
         */
        switch ( $units ) {
            case 'miles':
                //radius of the great circle in miles
                $gr_circle_radius = 3959;
            break;
            case 'kilometers':
                //radius of the great circle in kilometers
                $gr_circle_radius = 6371;
            break;
        }

        /*
         *  Support the selection of certain fields
         */
        if( ! $fields ) {
            $fields = array( 'accounts.*,tagging_tagged.tag_name,tagging_tagged.tag_slug' );
        }

        /*
         *  Generate the select field for disctance
         */
        $distance_select = sprintf(
                                    "
                                    ROUND(( %d * acos( cos( radians(%s) ) " .
                                            " * cos( radians( lat ) ) " .
                                            " * cos( radians( lng ) - radians(%s) ) " .
                                            " + sin( radians(%s) ) * sin( radians( lat ) ) " .
                                        " ) " .
                                    ")
                                    , 2 ) " .
                                    "AS distance
                                    ",
                                    $gr_circle_radius,
                                    $lat,
                                    $lng,
                                    $lat
                                   );
            $data = $query->select( DB::raw( implode( ',' ,  $fields ) . ',' .  $distance_select  ) )
            ->where('training_date', '!=', '')
            ->having( 'distance', '<=', $max_distance )
            ->orderBy( 'distance', 'ASC' )->get();


/*
            ->join('tagging_tagged', function ($join) use ($tag){
                $join->on('tagging_tagged.taggable_id', '=', 'accounts.id');
            })
            ->when($tag, function ($query, $tag) {
                return $query->where('tagging_tagged.tag_slug', $tag);
            })
*/
//            ->having( 'distance', '<=', $max_distance )
//            ->orderBy( 'distance', 'ASC' )->get();

        //echo '<pre>';
        //echo $query->toSQL();
        //echo $distance_select;
        //echo '</pre>';
        //die();
        //
        //$queries = DB::getQueryLog();
        //$last_query = end($queries);
        //var_dump($last_query);
        //die();
        return $data;
    }
}
