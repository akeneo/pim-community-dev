<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\JobInstanceNames;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Component\Console\CommandLauncher;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class InitJobInstancesCommand extends ContainerAwareCommand
{
    /** @var string */
    public const NAME = 'pimee:franklin-insights:init-job-instances';

    /** @var CommandLauncher */
    private $commandLauncher;

    /** @var JobInstanceRepository */
    private $jobInstanceRepository;

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName(self::NAME);
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        $this->jobInstanceRepository = $this->getContainer()->get('pim_enrich.repository.job_instance');
        $this->commandLauncher = $this->getContainer()->get('pim_catalog.command_launcher');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        if (!$this->isJobInstanceAlreadyCreated(JobInstanceNames::SUBSCRIBE_PRODUCTS)) {
            $this->createJobInstance(JobInstanceNames::SUBSCRIBE_PRODUCTS, 'mass_edit', $output);
        }

        if (!$this->isJobInstanceAlreadyCreated(JobInstanceNames::UNSUBSCRIBE_PRODUCTS)) {
            $this->createJobInstance(JobInstanceNames::UNSUBSCRIBE_PRODUCTS, 'mass_edit', $output);
        }

        if (!$this->isJobInstanceAlreadyCreated(JobInstanceNames::FETCH_PRODUCTS)) {
            $this->createJobInstance(JobInstanceNames::FETCH_PRODUCTS, 'franklin_insights', $output);
        }

        if (!$this->isJobInstanceAlreadyCreated(JobInstanceNames::REMOVE_ATTRIBUTES_FROM_MAPPING)) {
            $this->createJobInstance(JobInstanceNames::REMOVE_ATTRIBUTES_FROM_MAPPING, 'franklin_insights', $output);
        }

        if (!$this->isJobInstanceAlreadyCreated(JobInstanceNames::REMOVE_ATTRIBUTE_OPTION_FROM_MAPPING)) {
            $this->createJobInstance(
                JobInstanceNames::REMOVE_ATTRIBUTE_OPTION_FROM_MAPPING,
                'franklin_insights',
                $output
            );
        }

        if (!$this->isJobInstanceAlreadyCreated(JobInstanceNames::RESUBSCRIBE_PRODUCTS)) {
            $this->createJobInstance(JobInstanceNames::RESUBSCRIBE_PRODUCTS, 'franklin_insights', $output);
        }

        if (!$this->isJobInstanceAlreadyCreated(JobInstanceNames::IDENTIFY_PRODUCTS_TO_RESUBSCRIBE)) {
            $this->createJobInstance(JobInstanceNames::IDENTIFY_PRODUCTS_TO_RESUBSCRIBE, 'franklin_insights', $output);
        }

        if (!$this->isJobInstanceAlreadyCreated(JobInstanceNames::SYNCHRONIZE)) {
            $this->createJobInstance(JobInstanceNames::SYNCHRONIZE, 'franklin_insights', $output);
        }
    }

    /**
     * @param string $code
     *
     * @return bool
     */
    private function isJobInstanceAlreadyCreated(string $code): bool
    {
        return null !== $this->jobInstanceRepository->findOneBy(['code' => $code]);
    }

    /**
     * Launches a command to create job instance.
     *
     * @param string $jobName
     * @param string $jobType
     * @param OutputInterface $output
     */
    private function createJobInstance(string $jobName, string $jobType, OutputInterface $output): void
    {
        $result = $this->commandLauncher->executeForeground(
            sprintf(
                '%s "%s" "%s" "%s" "%s"',
                'akeneo:batch:create-job',
                'Franklin Insights Connector',
                $jobName,
                $jobType,
                $jobName
            )
        );

        if (0 !== $result->getCommandStatus()) {
            $output->writeln($result->getCommandOutput());
            throw new \RuntimeException(
                sprintf(
                    'Could not create job "%s" of type "%s"',
                    $jobName,
                    $jobType
                )
            );
        }
    }
}
