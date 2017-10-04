<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Portal\Bakery;

use Carbon\Carbon;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\System\Bakery\BaseCommand;

/**
 * Bakery command to push applications stats to slack.
 *
 * @author Kai Schröer (https://schroeer.co)
 */
class Slack extends BaseCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('slack')
            ->setDescription('Push the number of applications to slack');
    }
    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Push application stats to Slack');

        // Need to touch the config service first to load dotenv values
        /** @var \UserFrosting\Config\Config $config */
        $config = $this->ci->config;

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        $applicationsCnt = $classMapper->staticMethod('application', 'count');
        $applicationsToday = $classMapper->staticMethod(
            'application',
            'whereDate',
            'created_at',
            '=',
            Carbon::today()->toDateString()
        )->count();
        $applicationsYesterday = $classMapper->staticMethod(
            'application',
            'whereDate',
            'created_at',
            '=',
            Carbon::yesterday()->toDateString()
        )->count();
        $applicationsMissing = $classMapper->staticMethod(
            'user',
            'whereNotIn',
            'id',
            $classMapper->staticMethod(
                'application',
                'pluck',
                'user_id'
            )->all()
        )->where('flag_verified', '=', 1)->get()->filter(function (User $user, $key) {
            $permissions = $user->getCachedPermissions();
            // Everyone who has access to the dashboard is a team member.
            return !isset($permissions['uri_dashboard']);
        })->count();

        $data = 'payload={"text": "We have *' . $applicationsCnt . '* applications yet.\n' .
                '*' . $applicationsToday . '* of them were created today and *' . $applicationsYesterday . '* yesterday.\n' .
                '*' . $applicationsMissing . '* users still need to create their application. :bar_chart:"}';

        $webHookUrl = getenv('SLACK_WEBHOOK');

        if ($webHookUrl !== false && $webHookUrl !== '') {
            $ch = curl_init(getenv('SLACK_WEBHOOK'));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            curl_close($ch);

            $this->io->success('Slack message sent successful! Response: ' . $result);
        } else {
            $this->io->error('No Slack webhook url found in your .env file!');
        }
    }
}
