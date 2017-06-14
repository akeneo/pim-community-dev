<?php

namespace Pim\Bundle\ConnectorBundle\Command;

use Akeneo\Bundle\BatchBundle\Command\BatchCommand as BaseBatchCommand;
use Akeneo\Bundle\BatchBundle\Monolog\Handler\BatchLogHandler;
use Akeneo\Bundle\BatchBundle\Notification\MailNotifier;
use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobParametersValidator;
use Akeneo\Component\Batch\Job\JobRegistry;
use Akeneo\Component\Batch\Job\JobRepositoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Validator\Validator\RecursiveValidator;

/**
 * Batch command with user
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class BatchCommand extends BaseBatchCommand
{
    /** @var UserProviderInterface */
    protected $userProvider;

    /** @var TokenStorage */
    protected $tokenStorage;

    /**
     * @param DebugLoggerInterface                  $logger
     * @param BatchLogHandler                       $batchLogHandler
     * @param IdentifiableObjectRepositoryInterface $jobInstanceRepository
     * @param RegistryInterface                     $doctrine
     * @param RecursiveValidator                    $validator
     * @param MailNotifier                          $notifier
     * @param JobParametersFactory                  $jobParametersFactory
     * @param JobParametersValidator                $jobParametersValidator
     * @param JobRegistry                           $jobRegistry
     * @param JobRepositoryInterface                $jobRepository
     * @param UserProviderInterface                 $userProvider
     * @param TokenStorage                          $tokenStorage
     */
    public function __construct(
        DebugLoggerInterface $logger,
        BatchLogHandler $batchLogHandler,
        IdentifiableObjectRepositoryInterface $jobInstanceRepository,
        RegistryInterface $doctrine,
        RecursiveValidator $validator,
        MailNotifier $notifier,
        JobParametersFactory $jobParametersFactory,
        JobParametersValidator $jobParametersValidator,
        JobRegistry $jobRegistry,
        JobRepositoryInterface $jobRepository,
        UserProviderInterface $userProvider,
        TokenStorage $tokenStorage
    ) {
        parent::__construct(
            $logger,
            $batchLogHandler,
            $jobInstanceRepository,
            $doctrine,
            $validator,
            $notifier,
            $jobParametersFactory,
            $jobParametersValidator,
            $jobRegistry,
            $jobRepository
        );

        $this->logger = $logger;
        $this->batchLogHandler = $batchLogHandler;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->doctrine = $doctrine;
        $this->validator = $validator;
        $this->mailNotifier = $notifier;
        $this->jobParametersFactory = $jobParametersFactory;
        $this->jobParametersValidator = $jobParametersValidator;
        $this->jobRegistry = $jobRegistry;
        $this->jobRepository = $jobRepository;
        $this->userProvider = $userProvider;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:batch:job')
            ->setDescription('Launch a registered job instance')
            ->addArgument('code', InputArgument::REQUIRED, 'Job instance code')
            ->addArgument('username', InputArgument::REQUIRED, 'Username')
            ->addArgument('execution', InputArgument::OPTIONAL, 'Job execution id')
            ->addOption(
                'config',
                'c',
                InputOption::VALUE_REQUIRED,
                'Override job configuration (formatted as json. ie: ' .
                'php app/console akeneo:batch:job -c "{\"filePath\":\"/tmp/foo.csv\"}" acme_product_import)'
            )
            ->addOption(
                'email',
                null,
                InputOption::VALUE_REQUIRED,
                'The email to notify at the end of the job execution'
            )
            ->addOption(
                'no-log',
                null,
                InputOption::VALUE_NONE,
                'Don\'t display logs'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');

        $user = $this->userProvider->loadUserByUsername($username);

        if (null === $user) {
            throw new UsernameNotFoundException();
        }

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->tokenStorage->setToken($token);

        parent::execute($input, $output);
    }
}
