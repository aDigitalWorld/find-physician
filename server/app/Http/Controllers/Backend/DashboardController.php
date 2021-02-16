<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Accounts;
use App\Repositories\Backend\AccountRepository;

/**
 * Class DashboardController.
 */
class DashboardController extends Controller
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
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $totalTags = Accounts::existingTags()->count();
        $totalAccounts = Accounts::where('active', 1)->count();
        $vars = array('totalTags' => $totalTags, 'totalAccounts' => $totalAccounts);
        return view('backend.dashboard', $vars)->withAccounts($this->accountRepository->getActivePaginated(25, 'zoho_sync_date', 'desc'));
    }
}
