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
    public const SUBSCRIBE_PRODUCTS_JOB_NAME = 'suggest_data_subscribe_products';
    public const UNSUBSCRIBE_PRODUCTS_JOB_NAME = 'suggest_data_unsubscribe_products';
    public const FETCH_PRODUCTS_JOB_NAME = 'suggest_data_fetch_products';
    /** @var CommandLauncher */
    private $commandLauncher;

    /** @var JobInstanceRepository */
    private $jobInstanceRepository;

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('pimee:suggest-data:init-job-instances');
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
        if (null === $this->jobInstanceRepository->findOneBy(['code' => 'suggest_data_subscribe_products'])) {
            $this->createJobInstanceForMassEdit('suggest_data_subscribe_products');
        }

        if (null === $this->jobInstanceRepository->findOneBy(['code' => 'suggest_data_unsubscribe_products'])) {
            $this->createJobInstanceForMassEdit('suggest_data_unsubscribe_products');
        }

        if (null === $this->jobInstanceRepository->findOneBy(['code' => 'suggest_data_fetch_products'])) {
            $this->createJobInstanceForMassEdit('suggest_data_fetch_products');
        }
    }

    /**
     * Launches a command to create job instance for mass edit job name.
     *
     * @param string $jobName
     */
    private function createJobInstanceForMassEdit(string $jobName): void
    {
        $this->commandLauncher->executeForeground(
            sprintf(
                '%s "%s" "%s" "%s" "%s"',
                'akeneo:batch:create-job',
                'Suggest Data Connector',
                $jobName,
                'mass_edit',
                $jobName
            )
        );
    }
}
