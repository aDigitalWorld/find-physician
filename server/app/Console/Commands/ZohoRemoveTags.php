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
use \Conner\Tagging\Taggable;
use \Mailjet\Resources;
use Mailjet\LaravelMailjet\Facades\Mailjet;

class ZohoRemoveTags extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature   = 'zoho:removeTags';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove all tags';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle() {
        $start = microtime(true);
//        $tags = Accounts::get();
        $total = 0;
        $tags = Accounts::withAnyTag(['Vivace Rf Microneedling'])->get();
        foreach ($tags as $tag) {
            $tag->untag();
            $total++;
        }
        $time_elapsed_secs = microtime(true) - $start;

        $subject = 'ZohoSync completed at Cartessa';
        $message = "
        <h2>Removed {$total} tags completed</h2><p>
        <p>Completed in {$time_elapsed_secs} seconds</p>";
        $headers = 'From: maps@cartessa.com' . "\r\n" .
                   'Reply-To: ithippyshawn@gmail.com' . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();
        //mail('ithippyshawn@gmail.com', $subject, $message, $headers);

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

}
