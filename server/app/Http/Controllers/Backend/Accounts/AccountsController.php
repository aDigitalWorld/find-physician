<?php

namespace app\Http\Controllers\Backend\Accounts;

use App\Models\Accounts;
use App\Http\Controllers\Controller;
use App\Events\Backend\Auth\User\UserDeleted;
use App\Repositories\Backend\Auth\RoleRepository;
use App\Repositories\Backend\Auth\UserRepository;
use App\Repositories\Backend\AccountRepository;
use Illuminate\Http\Request;
use App\Repositories\Backend\Auth\PermissionRepository;
use App\Http\Requests\Backend\Account\StoreAccountRequest;
use App\Http\Requests\Backend\Account\ManageAccountRequest;
use App\Http\Requests\Backend\Account\UpdateAccountRequest;
use Illuminate\Support\Facades\Artisan;
use League\CLImate\CLImate;
use League\CLImate\Util\Writer\StdOut;
use League\CLImate\Util\Output;
use View;
use Carbon\Carbon;
/**
 * Class AccountsController.
 */
class AccountsController extends Controller
{
    /**
     * @var AccountRepository
     */
    protected $accountRepository;

    /**
     * UserController constructor.
     *
     * @param UserRepository $accountRepository
     */
    public function __construct(AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    /**
     * UserController constructor.
     *
     * @param UserRepository $accountRepository
     */
    public function getTest(Request $request){
        $account = Accounts::first()->get();
        $actions = view('backend.accounts.includes.actions', ['account' => $account]);
        dd($actions);
    }

    /**
     * UserController constructor.
     *
     * @param UserRepository $accountRepository
     */
    public function getAccounts(Request $request){

        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length"); // Rows display per page

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column']; // Column index
        $columnName = $columnName_arr[$columnIndex]['data']; // Column name
        $columnSortOrder = $order_arr[0]['dir']; // asc or desc
        $searchValue = $search_arr['value']; // Search value

        // Total records
        $totalRecords = Accounts::select('count(*) as allcount')->count();
        $totalRecordswithFilter = Accounts::select('count(*) as allcount')->where('name', 'like', '%' .$searchValue . '%')->count();

        // Fetch records
        $records = Employees::orderBy($columnName,$columnSortOrder)
            ->where('accounts.name', 'like', '%' .$searchValue . '%')
            ->select('accounts.*')
            ->with('tags')
            ->skip($start)
            ->take($rowperpage)
            ->get();

        $data_arr = array();
        foreach($records as $record){
            $id = $record->id;
            $username = $record->username;
            $name = $record->name;
            $email = $record->email;

            $data_arr[] = array(
              "id" => $id,
              "username" => $username,
              "name" => $name,
              "email" => $email
            );
        }
        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordswithFilter,
            "aaData" => $data_arr
        );
        echo json_encode($response);
        exit;
    }

    function accountsList(Request $request){

        $columns = array(
            0 =>'name',
            1 =>'city',
            2 =>'state',
            3 =>'country',
            4 =>'training_date',
            5 =>'tags',
            6 =>'active',
            7 =>'modified_at',
        );
        $totalData = Accounts::count();
        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir   = $request->input('order.0.dir');
        $tagSearch = false;

        if(empty($request->input('search.value')))
        {
            $accounts = Accounts::offset($start)
                         ->active()
                         ->limit($limit)
                         ->orderBy($order,$dir)
                         ->get();
        }else {
            $search = $request->input('search.value');
            $tags = Accounts::existingTags()->all();
            foreach ($tags as $tag) {
                if (strtolower($tag->name) == strtolower($search) OR strtolower($tag->slug) == strtolower($search)) {
                    $tagSearch = true;
                }
            }

            if (!$tagSearch) {
                $accounts = Accounts::where('name','LIKE',"%{$search}%")
                                ->orWhere('city', 'LIKE',"%{$search}%")
                                ->orWhere('training_date', 'LIKE',"%{$search}%")
                                ->orWhere('state', 'LIKE',"%{$search}%")
                                ->orWhere('country', 'LIKE',"%{$search}%")
                                ->active()
                                ->offset($start)
                                ->limit($limit)
                                ->orderBy($order,$dir)
                                ->get();

                $totalFiltered = Accounts::where('name','LIKE',"%{$search}%")
                                ->orWhere('city', 'LIKE',"%{$search}%")
                                ->orWhere('training_date', 'LIKE',"%{$search}%")
                                ->orWhere('state', 'LIKE',"%{$search}%")
                                ->orWhere('country', 'LIKE',"%{$search}%")
                                ->active()
                                ->limit(1000)
                                ->offset($start)
                                ->count();
            } else {

                $accounts = Accounts::withAnyTag([$search])
                                ->active()
                                ->offset($start)
                                ->limit($limit)
                                ->orderBy($order,$dir)
                                ->get();

                $totalFiltered = Accounts::withAnyTag([$search])
                                ->active()
                                ->limit(1000)
                                ->offset($start)
                                ->count();
            }
        }

        $data = array();
        if(!empty($accounts))
        {
            foreach ($accounts as $key=>$account)
            {

                $status = View::make('backend.accounts.includes.status', ['account' => $account]);
                $status = $status->render();
                $actions = View::make('backend.accounts.includes.actions', ['account' => $account]);
                $actions = $actions->render();
                $link = route('admin.account.show', [$account]);
                $name = "<div><a href=\"{$link}\">{$account->name}</a></div>" . PHP_EOL;
                $nestedData['name'] = $name;
                $nestedData['city'] = $account->city;
                $nestedData['state'] = $account->state;
                $nestedData['country'] = $account->country;
                $nestedData['training_date'] = $account->training_date;
                $nestedData['tags'] = outputTags($account->tagNames(), true);
                $nestedData['active'] = $status;
                $nestedData['modified_at'] = $account->modified_at->diffForHumans();
                $nestedData['action'] = $actions;
                $data[] = $nestedData;
            }

        }
        $json_data = array(
        "draw"            => intval($request->input('draw')),
        "recordsTotal"    => intval($totalData),
        "recordsFiltered" => intval($totalFiltered),
        "data"            => $data
        );
        echo json_encode($json_data);
     }

    /**
     * @param ManageAccountRequest $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(ManageAccountRequest $request)
    {
        return view('backend.accounts.index')
            ->withAccounts($this->accountRepository->getActivePaginated(25, 'id', 'asc'));
    }

    /**
     * @param ManageAccountRequest    $request
     * @param RoleRepository       $roleRepository
     * @param PermissionRepository $permissionRepository
     *
     * @return mixed
     */
    public function create(ManageAccountRequest $request, RoleRepository $roleRepository, PermissionRepository $permissionRepository)
    {
        $tags = Accounts::existingTags()->pluck('name');
        return view('backend.accounts.create', ['tags' => $tags])->with('tags');
    }

    /**
     * @param StoreAccountRequest $request
     *
     * @throws \Throwable
     * @return mixed
     */
    public function store(StoreAccountRequest $request)
    {
        $this->accountRepository->create($request->only(
            'name',
            'street',
            'city',
            'state',
            'zipcode',
            'country',
            'website',
            'lat',
            'lng',
            'training_date',
            'phone',
            'formatted_address',
            'tags',
            'active',
            'override',
        ));

        return redirect()->route('admin.accounts.index')->withFlashSuccess(__('alerts.backend.accounts.created'));
    }

    /**
     * @param ManageAccountRequest $request
     * @param Account              $account
     *
     * @return mixed
     */
    public function show(ManageAccountRequest $request, Accounts $account)
    {
        return view('backend.accounts.show')->withAccount($account)->with('tags');
    }

    /**
     * @param ManageAccountRequest    $request
     * @param RoleRepository       $roleRepository
     * @param PermissionRepository $permissionRepository
     * @param Accounts             $account
     *
     * @return mixed
     */
    public function edit(ManageAccountRequest $request, Accounts $account)
    {
        $allTags = Accounts::existingTags();
        return view('backend.accounts.edit', ['allTags' => $allTags])
            ->withAccount($account)->with('tags');
    }

    /**
     * @param UpdateAccountRequest $request
     * @param Account              $account
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Throwable
     * @return mixed
     */
    public function update(UpdateAccountRequest $request, Accounts $account)
    {
        $this->accountRepository->update($account, $request->only(
            'name',
            'street',
            'city',
            'state',
            'zipcode',
            'country',
            'website',
            'lat',
            'lng',
            'training_date',
            'phone',
            'created_at',
            'modified_at',
            'formatted_address',
            'active',
            'override',
            'tags',
        ));

        return redirect()->route('admin.accounts.index')->withFlashSuccess(__('alerts.backend.accounts.updated'));
    }

    /**
     * @param ManageAccountRequest $request
     * @param Account              $account
     *
     * @throws \Exception
     * @return mixed
     */
    public function destroy(ManageAccountRequest $request, Accounts $account)
    {
        $this->accountRepository->deleteById($account->id);
        return redirect()->route('admin.account.deleted')->withFlashSuccess(__('alerts.backend.accounts.deleted'));
    }

    /**
     * @param ManageAccountRequest $request
     * @param Account              $account
     *
     * @throws \Exception
     * @return mixed
     */
    public function startSync(ManageAccountRequest $request)
    {
        Artisan::call('zoho:syncDevices');
        return redirect()->route('admin.account.index')->withFlashSuccess(__('alerts.backend.accounts.sync_started'));
    }
}
