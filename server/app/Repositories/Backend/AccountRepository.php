<?php

namespace app\Repositories\Backend;

use Illuminate\Support\Facades\DB;
use App\Exceptions\GeneralException;
use App\Repositories\BaseRepository;
use Spatie\Permission\Models\Permission;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Events\Backend\Accounts\AccountCreated;
use App\Models\Accounts;
/**
 * Class AccountRepository.
 */
class AccountRepository extends BaseRepository
{
    use \Conner\Tagging\Taggable;
    /**
     * PermissionRepository constructor.
     *
     * @param  Permission  $model
     */
    public function __construct(Accounts $model)
    {
        $this->model = $model;
    }

    public static function outputTags($tags){
        $tagfield = '';
        $tagfield .= implode(" ,", $tags);
        return $tagfield;
    }

    /**
     * @return mixed
     */
    public function getUnconfirmedCount() : int
    {
        return $this->model
            ->where('training_date !=', '')
            ->count();
    }


    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return bool
     */
    public function isConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * @param int    $paged
     * @param string $orderBy
     * @param string $sort
     *
     * @return mixed
     */
    public function getActivePaginated($paged = 25, $orderBy = 'created_at', $sort = 'desc') : LengthAwarePaginator
    {
        //
        return $this->model
            ->with('tagged')
            ->active()
            ->orderBy($orderBy, $sort)
            ->paginate($paged);
    }

    /**
     * @param int    $paged
     * @param string $orderBy
     * @param string $sort
     *
     * @return LengthAwarePaginator
     */
    public function getInactivePaginated($paged = 25, $orderBy = 'created_at', $sort = 'desc') : LengthAwarePaginator
    {
        return $this->model
            ->with('tagged')
            ->active(false)
            ->orderBy($orderBy, $sort)
            ->paginate($paged);
    }

    /**
     * @param int    $paged
     * @param string $orderBy
     * @param string $sort
     *
     * @return LengthAwarePaginator
     */
    public function getDeletedPaginated($paged = 25, $orderBy = 'created_at', $sort = 'desc') : LengthAwarePaginator
    {
        return $this->model
            ->with('tagged')
            ->onlyTrashed()
            ->orderBy($orderBy, $sort)
            ->paginate($paged);
    }

    /**
     * @param array $data
     *
     * @throws \Exception
     * @throws \Throwable
     * @return Account
     */
    public function create(array $data) : Accounts
    {
        return DB::transaction(function () use ($data) {

            $address = formatAddress($data['street'], $data['city'], $data['state'], $data['zipcode'], $data['country']);
            $geo     = geocodeFree($address);
            $data['lat'] = 1;
            $data['lng'] = 1;
            $data['phone'] = isset($data['phone']) && !empty($data['phone']) ? $data['phone'] : '';
            $data['website'] = isset($data['website']) && !empty($data['website']) ? $data['website'] : '';
            $data['training_date'] = isset($data['training_date']) && !empty($data['training_date']) ? $data['training_date'] : '';
            if (is_object($geo)) {
                $data['lat'] = $geo->lat;
                $data['lng'] = $geo->lng;
                $address = $geo->formatted_address;
            }

            $account = $this->model::create([
                'name'              => $data['name'],
                'street'            => $data['street'],
                'city'              => $data['city'],
                'state'             => $data['state'],
                'zipcode'           => $data['zipcode'],
                'country'           => $data['country'],
                'website'           => $data['website'],
                'phone'             => $data['phone'],
                'training_date'     => $data['training_date'],
                'formatted_address' => $address,
                'lat'               => $data['lat'],
                'lng'               => $data['lng'],
                'active'   => isset($data['active']) && $data['active'] === '1',
                'override' => isset($data['override']) && $data['override'] === '1',
            ]);

            // See if adding any additional permissions
            if (! isset($data['tags']) || ! count($data['tags'])) {
                $data['tags'] = [];
            }

            if ($account) {
                // Add selected roles/permissions
                //$account->syncRoles($data['tags']);
                $account->retag($data['tags']);
///                event(new AccountCreated($account));

                return $account;
            }

            throw new GeneralException(__('exceptions.backend.access.accounts.create_error'));
        });
    }


    /**
     * @param Account  $account
     * @param array $data
     *
     * @throws GeneralException
     * @throws \Exception
     * @throws \Throwable
     * @return Account
     */
    public function update(Accounts $account, array $data) : Accounts
    {

        // See if adding any additional permissions
        if (! isset($data['tags']) || ! count($data['tags'])) {
            $data['tags'] = [];
        }

        return DB::transaction(function () use ($account, $data) {

            $address = $account->formatted_address;
            $data['lat'] = $account->lat;
            $data['lng'] = $account->lng;
            $data['phone'] = isset($data['phone']) && !empty($data['phone']) ? $data['phone'] : $account->phone;
            $data['website'] = isset($data['website']) && !empty($data['website']) ? $data['website'] : $account->website;
            $data['training_date'] = isset($data['training_date']) && !empty($data['training_date']) ? $data['training_date'] : $account->training_date;
            if ($account->formatted_address != $data['formatted_address'] || empty($data['lat']) OR empty($data['lat'])) {
                $address = formatAddress($data['street'], $data['city'], $data['state'], $data['zipcode'], $data['country']);
                $geo    = geocodeFree($address);
                if (is_object($geo)) {
                    $data['lat'] = $geo->lat;
                    $data['lng'] = $geo->lng;
                    $address = $geo->formatted_address;
                }
            }

            if ($account->update([
                'name'              => $data['name'],
                'street'            => $data['street'],
                'city'              => $data['city'],
                'state'             => $data['state'],
                'zipcode'           => $data['zipcode'],
                'country'           => $data['country'],
                'website'           => $data['website'],
                'phone'             => $data['phone'],
                'training_date'     => $data['training_date'],
                'lat'               => $data['lat'],
                'lng'               => $data['lng'],
                'formatted_address' => $address,
                'active'   => isset($data['active']) && $data['active'] === '1',
                'override' => isset($data['override']) ? $data['override'] : 0,
            ])) {
                // Add selected roles/permissions
                $account->retag($data['tags']);
                //event(new AccountUpdated($account));

                return $account;
            }

            throw new GeneralException(__('exceptions.backend.access.accounts.update_error'));
        });
    }


    /**
     * @param Account $account
     * @param      $status
     *
     * @throws GeneralException
     * @return Account
     */
    public function mark(Accounts $account, $status) : Accounts
    {
        $account->active = $status;

        switch ($status) {
            case 0:
                // event(new AccountDeactivated($account));
            break;
            case 1:
                // event(new AccountReactivated($account));
            break;
        }

        if ($account->save()) {
            return $account;
        }

        throw new GeneralException(__('exceptions.backend.access.accounts.mark_error'));
    }

}
