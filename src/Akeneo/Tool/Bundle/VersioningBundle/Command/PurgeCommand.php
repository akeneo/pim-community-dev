<?php

namespace Pim\Bundle\VersioningBundle\Command;

use InvalidArgumentException;
use Monolog\Handler\StreamHandler;
use Pim\Bundle\VersioningBundle\EventSubscriber\PurgeProgressBarAdvancerSubscriber;
use Pim\Bundle\VersioningBundle\Purger\VersionPurgerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
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
class PurgeCommand extends ContainerAwareCommand
{
    const DEFAULT_MORE_THAN_DAYS = 90;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:versioning:purge')
            ->setDescription('List versions of any updated entities')
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

        if (null === $input->getOption('more-than-days') && null === $input->getOption('less-than-days')) {
            $input->setOption('more-than-days', self::DEFAULT_MORE_THAN_DAYS);
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
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $noDebug = $input->getOption('no-debug');
        if (!$noDebug) {
            $logger = $this->getContainer()->get('logger');
            $logger->pushHandler(new StreamHandler('php://stdout'));
        }

        $isForced = $input->getOption('force');
        $moreThanDays = null !== $input->getOption('more-than-days') ? (int) $input->getOption('more-than-days') : null;
        $lessThanDays = null !== $input->getOption('less-than-days') ? (int) $input->getOption('less-than-days') : null;

        $resourceName = $input->hasArgument('entity') ? $input->getArgument('entity') : null;
        $resourceNameLabel = '';
        if (null !== $resourceName) {
            $purgeOptions['resource_name'] = $resourceName;
            $resourceNameLabel = sprintf('of %s ', $resourceName);
        }

        $purgeOptions['days_number'] = null !== $lessThanDays ? $lessThanDays : $moreThanDays;
        $purgeOptions['date_operator'] = null !== $lessThanDays ? '>' : '<';
        $operatorLabel = null !== $lessThanDays ? 'younger' : 'older';

        $purger = $this->getVersionPurger();

        $totalVersions = $purger->getVersionsToPurgeCount($purgeOptions);

        $output->writeln(
            sprintf(
                '<info>You are about to process %d versions %s%s than %d days.</info>',
                $totalVersions,
                $resourceNameLabel,
                $operatorLabel,
                $purgeOptions['days_number']
            )
        );

        if (!$isForced) {
            $helper = $this->getHelper('question');
            $question = new ConfirmationQuestion(
                'This operation may take some time to complete. Are you sure you want to purge? [Y/n] ',
                false,
                '/^y/i'
            );

            if (!$helper->ask($input, $output, $question)) {
                $output->writeln('Abort purge operation.');

                return;
            }
        }

        $progressBar = new ProgressBar($output, $totalVersions);
        $this->getPurgeProgressBarAdvancer()->setProgressBar($progressBar);

        $purgeVersionsCount = $purger->purge($purgeOptions);

        $progressBar->finish();

        $output->writeln('');
        $output->writeln(
            sprintf(
                '<info>Successfully deleted %d versions.</info>',
                $purgeVersionsCount
            )
        );
    }

    /**
     * @return VersionPurgerInterface
     */
    protected function getVersionPurger()
    {
        return $this->getContainer()->get('pim_versioning.purger.version');
    }

    /**
     * @return PurgeProgressBarAdvancerSubscriber
     */
    protected function getPurgeProgressBarAdvancer()
    {
        return $this->getContainer()->get('pim_versioning.event_subscriber.purge_progress_bar_advancer');
    }
}
