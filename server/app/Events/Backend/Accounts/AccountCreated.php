<?php

namespace App\Events\Backend\Accounts;

use Illuminate\Queue\SerializesModels;

/**
 * Class AccountCreated.
 */
class AccountCreated
{
    use SerializesModels;

    /**
     * @var
     */
    public $account;

    /**
     * @param $account
     */
    public function __construct($account)
    {
        $this->account = $account;
    }
}
