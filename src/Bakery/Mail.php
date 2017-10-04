<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Portal\Bakery;

use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Core\Mail\EmailRecipient;
use UserFrosting\Sprinkle\Core\Mail\TwigMailMessage;
use UserFrosting\System\Bakery\BaseCommand;

/**
 * Bakery command to send application reminder emails to users who registered but did not apply.
 *
 * @author Kai Schröer (https://schroeer.co)
 */
class Mail extends BaseCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('mail')
            ->setDescription('Send emails to users')
            ->setDefinition(
                new InputDefinition([
                    new InputOption('application', 'app', InputArgument::OPTIONAL, 'with application (with), without application (without), all is default', 'all'),
                    new InputOption('template', 'tpl', InputArgument::OPTIONAL, 'Mail-Template (templates/mail)', 'application-remind')
                ])
            );
    }
    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Send emails to users');

        /** @var \UserFrosting\Config\Config $config */
        $config = $this->ci->config;

        // For command line usage we need to set the public uri manually
        $config['site.uri.public'] = $config['site.uri.cli'];

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        // Get command line options
        $template = $input->getOption('template');
        $application = $input->getOption('application');

        if (file_exists(__DIR__ . "/../../templates/mail/{$template}.html.twig")) {
            switch ($application) {
                case 'with':
                    $recipients = $classMapper->staticMethod(
                        'user',
                        'whereIn',
                        'id',
                        $classMapper->staticMethod(
                            'application',
                            'pluck',
                            'user_id'
                        )->all()
                    )->get()->filter(function (User $user, $key) {
                        $permissions = $user->getCachedPermissions();
                        // Everyone who has access to the dashboard is a team member.
                        return !isset($permissions['uri_dashboard']);
                    })->all();
                    break;
                case 'without':
                    $recipients = $classMapper->staticMethod(
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
                    })->all();
                    break;
                default:
                    // We want admins to receive the email too here.
                    $recipients = $classMapper->staticMethod(
                        'user',
                        'where',
                        'flag_verified',
                        '=',
                        1
                    )->get();
            }

            foreach ($recipients as $recipient) {
                try {
                    // Create and send application reminder email
                    $message = new TwigMailMessage($this->ci->view, "mail/{$template}.html.twig");

                    $message->from($config['address_book.admin'])
                        ->addEmailRecipient(new EmailRecipient($recipient->email, $recipient->full_name))
                        ->addParams([
                            'user' => $recipient
                        ]);

                    $this->ci->mailer->send($message);

                    $this->io->writeln('Email to: ' . $recipient->user_name . ' (' . $recipient->email . ')');
                } catch (\phpmailerException $ex) {
                    $this->io->error('Error: ' . $ex->getMessage());
                }
            }
        } else {
            $this->io->error('Given template does not exist.');
        }
    }
}
