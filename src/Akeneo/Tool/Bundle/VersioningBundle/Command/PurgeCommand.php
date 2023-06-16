<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Command;

use Akeneo\Tool\Bundle\BatchBundle\JobExecution\CreateJobExecutionHandlerInterface;
use Akeneo\Tool\Bundle\BatchBundle\JobExecution\ExecuteJobExecutionHandlerInterface;
use InvalidArgumentException;
use Monolog\Handler\StreamHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

/**
 * Purge version of entities
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PurgeCommand extends Command
{
    protected static $defaultName = 'pim:versioning:purge';
    protected static $defaultDescription = 'Purge versions of entities, except first and last versions.';

    private const JOB_CODE = 'versioning_purge';

    const DEFAULT_MORE_THAN_DAYS = 90;

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ExecuteJobExecutionHandlerInterface $jobExecutionRunner,
        private readonly CreateJobExecutionHandlerInterface $jobExecutionFactory,
        private readonly ?int $versioningRetentionInDays
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->addArgument(
                'entity',
                InputArgument::OPTIONAL,
                'Fully qualified classname of the entity to purge. (beware of overridden or custom entities)',
                null
            )
            ->addOption(
                'more-than-days',
                null,
                InputOption::VALUE_OPTIONAL,
                'Purges the versions that are older than the number of days'
            )
            ->addOption(
                'less-than-days',
                null,
                InputOption::VALUE_OPTIONAL,
                'Purges the versions that are younger than the number of days'
            )
            ->addOption(
                'batch-size',
                null,
                InputOption::VALUE_OPTIONAL,
                'Purges the versions by batch',
                1000
            )
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE,
                'No confirmation question. Directly purges versions for entity.'
            );
    }

    /**
     * {@inheritdoc}
     */
    public function interact(InputInterface $input, OutputInterface $output)
    {
        if (null !== $input->getOption('more-than-days') && null !== $input->getOption('less-than-days')) {
            throw new InvalidArgumentException(
                'Options --more-than-days and --less-than-days cannot be used together.'
            );
        }

        $resourceFQCN = $input->hasArgument('entity') ? $input->getArgument('entity') : null;
        $isForced = $input->getOption('force');

        if (null !== $resourceFQCN && !class_exists($resourceFQCN)) {
            $errorCallback = function ($resourceFQCN) use ($output) {
                $output->writeln('<info>Abort the purge operation. Nothing has been deleted from the database.</info>');
                throw new InvalidArgumentException(
                    sprintf(
                        'Entity %s is not a valid fully qualified class name.',
                        $resourceFQCN
                    )
                );
            };

            $output->writeln(sprintf('<info>"%s" is not a valid resource name.</info>', $resourceFQCN));
            if ($isForced) {
                $errorCallback($resourceFQCN);
            }

            $helper = $this->getHelper('question');
            $question = new Question('<question>Please insert a valid fully qualified class name: </question>');
            $question->setValidator(function ($answer) {
                if (!class_exists($answer)) {
                    throw new \RuntimeException(
                        'The fully qualified class name does not exist. Please choose a value from the table above.'
                    );
                }

                return $answer;
            });
            $question->setMaxAttempts(2);

            $resourceFQCN = $helper->ask($input, $output, $question);
            if (!$resourceFQCN) {
                $errorCallback($resourceFQCN);
            }

            $input->setArgument('entity', $resourceFQCN);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->logger->pushHandler(new StreamHandler('php://stdout'));

        $isForced = $input->getOption('force');
        $moreThanDays = null !== $input->getOption('more-than-days') ? (int)$input->getOption('more-than-days') : null;
        $lessThanDays = null !== $input->getOption('less-than-days') ? (int)$input->getOption('less-than-days') : null;

        if (null === $moreThanDays && null === $lessThanDays) {
            $moreThanDays = $this->versioningRetentionInDays;
            if (null === $moreThanDays) {
                $moreThanDays = self::DEFAULT_MORE_THAN_DAYS;
            }
        }

        $resourceName = $input->hasArgument('entity') ? $input->getArgument('entity') : null;
        $resourceNameLabel = null !== $resourceName ? sprintf('of %s ', $resourceName) : '';
        $operatorLabel = null !== $lessThanDays ? 'younger' : 'older';
        $daysNumber = null !== $lessThanDays ? $lessThanDays : $moreThanDays;

        $output->writeln(
            sprintf(
                '<info>You are about to process versions %s%s than %d days.</info>',
                $resourceNameLabel,
                $operatorLabel,
                $daysNumber,
            )
        );

        if (!$isForced) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                'This operation may take some time to complete. Are you sure you want to purge? [y/N] ',
                false,
                '/^y/i'
            );

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('Abort purge operation.');

                return Command::FAILURE;
            }
        }

        $batchConfig = [
            'entity' => $resourceName,
            'batch-size' => (int)$input->getOption('batch-size'),
            'more-than-days' => $moreThanDays,
            'less-than-days' => $lessThanDays,
        ];

        $jobExecution = $this->jobExecutionFactory->createFromBatchCode(self::JOB_CODE, $batchConfig, null);
        $this->jobExecutionRunner->executeFromJobExecutionId($jobExecution->getId());

        return Command::SUCCESS;
    }
}
