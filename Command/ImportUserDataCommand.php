<?php

namespace SAM\CommonBundle\Command;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Console\Command\LockableTrait;
use Psr\Log\LoggerInterface;

/**
 * Class ImportUserDataCommand.
 */
class ImportUserDataCommand extends Command
{
    use LockableTrait;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var ObjectManager
     */
    private $om;

    /** @var LoggerInterface */
    private $logger;

    /**
     * CreateProductsCommand class constructor
     *
     * @param KernelInterface $kernel
     * @param ObjectManager   $om
     */
    public function __construct(KernelInterface $kernel, ObjectManager $om, LoggerInterface $logger)
    {
        parent::__construct();

        $this->kernel = $kernel;
        $this->om = $om;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setName('app:user:import')
            ->setDescription('Import user data (contacts and notes)');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->lock()) {
            $output->writeln('<error>The command is already running in another process.</error>');

            return 0;
        }

        try {
            $users = $this->om->getRepository('user')->findAll();

            $application = new Application($this->kernel);
            $application->setAutoExit(false);

            /** @var User $user */
            foreach ($users as $user) {
                $output->writeln(sprintf('Import contacts for user %s', $user->getEmail()));

                try {
                    $childInput = new ArrayInput([
                        'command' => 'app:contacts:import',
                        'user' => $user->getId(),
                    ]);

                    $childOutput = new BufferedOutput();
                    $application->run($childInput, $childOutput);
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage(), [
                        'context' => ImportUserDataCommand::class,
                        'module' => 'command.import_user.import_user_contacts',
                        'user' => $user,
                        'exception' => $e
                    ]);
                    $output->writeln($e->getMessage());
                    continue;
                }

                try {
                    $output->writeln(sprintf('Import contacts notes for user %s', $user->getEmail()));
                    $childInput = new ArrayInput([
                        'command' => 'app:contacts-note:import',
                        'user' => $user->getId(),
                    ]);

                    $childOutput = new BufferedOutput();
                    $application->run($childInput, $childOutput);
                } catch (\Exception $e) {
                    $this->logger->error($e->getMessage(), [
                        'context' => ImportUserDataCommand::class,
                        'module' => 'command.import_user.import_user_note',
                        'user' => $user,
                        'exception' => $e
                    ]);
                    $output->writeln($e->getMessage());
                    continue;
                }
            }
        }
        catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), [
                'context' => ImportUserDataCommand::class,
                'module' => 'command.import_users',
                'exception' => $e
            ]);
            $output->writeln('<error>An error occured during app:user:import execution :'.$e->getMessage().'</error>');
        } finally {
            $this->release();
        }
    }
}
