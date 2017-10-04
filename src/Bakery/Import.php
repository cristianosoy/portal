<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Portal\Bakery;

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use UserFrosting\System\Bakery\BaseCommand;

/**
 * Bakery command to import swot universities from checked out swot git repo.
 *
 * @author Kai Schröer (https://schroeer.co)
 */
class Import extends BaseCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('import')
            ->setDescription('Import universities from checked out swot git repo');
    }
    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Import universities from swot');

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->ci->classMapper;

        /** @var \UserFrosting\Config\Config $config */
        $config = $this->ci->config;

        /** @var \Illuminate\Filesystem\Filesystem $fs */
        $fs = new Filesystem();

        // Path of the domain directory from swot git repo
        $domainPath = $config['swot.domains_path'];

        // We store the domains form filesystem in an array to compare them with db
        $domainsFromFs = array();

        $this->io->writeln('Configured repository path: ' . $domainPath);
        $this->io->section('Changes');

        if ($fs->exists($domainPath)) {
            // Returns an array of http://api.symfony.com/3.1/Symfony/Component/Finder/SplFileInfo.html
            $domainFiles = $fs->allFiles($domainPath);

            // Get Name from text file and domain from path and save it as an university
            foreach ($domainFiles as $file) {
                // Build university domain from file path (ae/ac/ajman.txt => ajman.ac.ae)
                $data['domain'] = join(
                    '.',
                    array_reverse(
                        explode(
                            \UserFrosting\DS,
                            str_replace(
                                '.txt',
                                '',
                                $file->getRelativePathname()
                            )
                        )
                    )
                );

                $domainsFromFs[] = $data['domain'];

                // Get the first line from txt file as university name
                $data['name'] = explode(
                    PHP_EOL,
                    str_replace(
                        '&amp;',
                        '&',
                        $file->getContents()
                    )
                )[0];

                $data['imported'] = true;

                $university = $classMapper->staticMethod('university', 'where', 'domain', $data['domain'])->first();
                if ($university !== null) {
                    $university->update($data);
                } else {
                    $university = $classMapper->createInstance('university', $data);
                    $university->save();
                    $this->io->writeln('Created: ' . $university->name);
                }
            }

            $domainsFromDb = $classMapper->staticMethod('university', 'where', 'imported', true)->pluck('domain')->all();

            // Returns all domains which need to be deleted
            $deletedDomains = array_diff($domainsFromDb, $domainsFromFs);

            foreach ($deletedDomains as $delete) {
                $university = $classMapper->staticMethod('university', 'where', 'domain', $delete)->first();
                if (!is_null($university)) {
                    $applicationCnt = $classMapper->staticMethod(
                        'application',
                        'where',
                        'university_id',
                        $university->id
                    )->count();
                    if ($applicationCnt === 0) {
                        $this->io->writeln('Deleted: ' . $university->name);
                        $university->delete();
                    } else {
                        $this->io->writeln($university->name . ' can not be deleted.');
                    }
                }
            }

            // Number of universities after the import
            $count = $classMapper->staticMethod('university', 'where', 'imported', true)->count();

            $this->io->success('Number of imported universities in the database: ' . $count);
        } else {
            $this->io->error('Configured repository path not found.');
        }
    }
}
