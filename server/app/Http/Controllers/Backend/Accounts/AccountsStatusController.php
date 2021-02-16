<?php

namespace app\Http\Controllers\Backend\Accounts;

use App\Models\Accounts;
use App\Http\Controllers\Controller;
use App\Repositories\Backend\AccountRepository;
use App\Http\Requests\Backend\Account\StoreAccountRequest;
use App\Http\Requests\Backend\Account\ManageAccountRequest;
use App\Http\Requests\Backend\Account\UpdateAccountRequest;

/**
 * Class AccountsStatusController.
 */
class AccountsStatusController extends Controller
{
    /**
     * @var AccountRepository
     */
    protected $accountRepository;

    /**
     * @param AccountRepository $accountRepository
     */
    public function __construct(AccountRepository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    /**
     * @param ManageAccountRequest $request
     *
     * @return mixed
     */
    public function getDeactivated(ManageAccountRequest $request)
    {
        return view('backend.accounts.deactivated')
            ->withAccounts($this->accountRepository->getInactivePaginated(25, 'id', 'asc'));
    }

    /**
     * @param ManageAccountRequest $request
     *
     * @return mixed
     */
    public function getDeleted(ManageAccountRequest $request)
    {
        return view('backend.accounts.deleted')
            ->withAccounts($this->accountRepository->getDeletedPaginated(25, 'id', 'asc'));
    }

    /**
     * @param ManageAccountRequest $request
     * @param Account              $account
     * @param                   $status
     *
     * @throws \App\Exceptions\GeneralException
     * @return mixed
     */
    public function mark(ManageAccountRequest $request, Accounts $account, $status)
    {
        $this->accountRepository->mark($account, (int) $status);

        return redirect()->route(
            (int) $status === 1 ?
            'admin.accounts.index' :
            'admin.accounts.deactivated'
        )->withFlashSuccess(__('alerts.backend.accounts.updated'));
    }

    /**
     * @param ManageAccountRequest $request
     * @param Account              $deletedAccount
     *
     * @throws \App\Exceptions\GeneralException
     * @throws \Throwable
     * @return mixed
     */
    public function delete(ManageAccountRequest $request, Accounts $deletedAccount)
    {
        $this->accountRepository->forceDelete($deletedAccount);

        return redirect()->route('admin.account.deleted')->withFlashSuccess(__('alerts.backend.accounts.deleted_permanently'));
    }

    /**
     * @param ManageAccountRequest $request
     * @param Account              $deletedAccount
     *
     * @throws \App\Exceptions\GeneralException
     * @return mixed
     */
    public function restore(ManageAccountRequest $request, Accounts $deletedAccount)
    {
        $this->accountRepository->restore($deletedAccount);

        return redirect()->route('admin.accounts.index')->withFlashSuccess(__('alerts.backend.accounts.restored'));
    }
}
