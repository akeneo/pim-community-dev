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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Symfony\Command;

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
    public const NAME = 'pimee:suggest-data:init-job-instances';

    private const SUBSCRIBE_PRODUCTS_JOB_NAME = 'suggest_data_subscribe_products';
    private const UNSUBSCRIBE_PRODUCTS_JOB_NAME = 'suggest_data_unsubscribe_products';
    private const FETCH_PRODUCTS_JOB_NAME = 'suggest_data_fetch_products';
    private const REMOVE_ATTRIBUTE_FROM_MAPPING_JOB_NAME = 'suggest_data_remove_attribute_from_mapping';

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
        if (!$this->isJobInstanceAlreadyCreated(self::SUBSCRIBE_PRODUCTS_JOB_NAME)) {
            $this->createJobInstance(self::SUBSCRIBE_PRODUCTS_JOB_NAME, 'mass_edit');
        }

        if (!$this->isJobInstanceAlreadyCreated(self::UNSUBSCRIBE_PRODUCTS_JOB_NAME)) {
            $this->createJobInstance(self::UNSUBSCRIBE_PRODUCTS_JOB_NAME, 'mass_edit');
        }

        if (!$this->isJobInstanceAlreadyCreated(self::FETCH_PRODUCTS_JOB_NAME)) {
            $this->createJobInstance(self::FETCH_PRODUCTS_JOB_NAME, 'franklin_insights');
        }

        if (!$this->isJobInstanceAlreadyCreated(self::REMOVE_ATTRIBUTE_FROM_MAPPING_JOB_NAME)) {
            $this->createJobInstance(self::REMOVE_ATTRIBUTE_FROM_MAPPING_JOB_NAME, 'franklin_insights');
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
     */
    private function createJobInstance(string $jobName, string $jobType): void
    {
        $this->commandLauncher->executeForeground(
            sprintf(
                '%s "%s" "%s" "%s" "%s"',
                'akeneo:batch:create-job',
                'Suggest Data Connector',
                $jobName,
                $jobType,
                $jobName
            )
        );
    }
}
