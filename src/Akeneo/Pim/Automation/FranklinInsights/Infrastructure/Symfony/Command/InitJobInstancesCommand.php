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
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class InitJobInstancesCommand extends Command
{
    /** @var string */
    public const NAME = 'pimee:franklin-insights:init-job-instances';

    protected static $defaultName = self::NAME;

    /** @var JobInstanceRepository */
    private $jobInstanceRepository;

    /** @var Connection */
    private $db;

    public function __construct(ObjectRepository $jobInstanceRepository, Connection $db)
    {
        parent::__construct();

        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->db = $db;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        if (!$this->isJobInstanceAlreadyCreated(JobInstanceNames::SUBSCRIBE_PRODUCTS)) {
            $this->createJobInstance(JobInstanceNames::SUBSCRIBE_PRODUCTS, 'mass_edit', $output, 'Mass subscribe products');
        }

        if (!$this->isJobInstanceAlreadyCreated(JobInstanceNames::UNSUBSCRIBE_PRODUCTS)) {
            $this->createJobInstance(JobInstanceNames::UNSUBSCRIBE_PRODUCTS, 'mass_edit', $output, 'Mass unsubscribe products');
        }

        if (!$this->isJobInstanceAlreadyCreated(JobInstanceNames::FETCH_PRODUCTS)) {
            $this->createJobInstance(JobInstanceNames::FETCH_PRODUCTS, 'franklin_insights', $output, JobInstanceNames::FETCH_PRODUCTS);
        }

        if (!$this->isJobInstanceAlreadyCreated(JobInstanceNames::REMOVE_ATTRIBUTES_FROM_MAPPING)) {
            $this->createJobInstance(JobInstanceNames::REMOVE_ATTRIBUTES_FROM_MAPPING, 'franklin_insights', $output, JobInstanceNames::REMOVE_ATTRIBUTES_FROM_MAPPING);
        }

        if (!$this->isJobInstanceAlreadyCreated(JobInstanceNames::REMOVE_ATTRIBUTE_OPTION_FROM_MAPPING)) {
            $this->createJobInstance(
                JobInstanceNames::REMOVE_ATTRIBUTE_OPTION_FROM_MAPPING,
                'franklin_insights',
                $output,
                JobInstanceNames::REMOVE_ATTRIBUTE_OPTION_FROM_MAPPING
            );
        }

        if (!$this->isJobInstanceAlreadyCreated(JobInstanceNames::RESUBSCRIBE_PRODUCTS)) {
            $this->createJobInstance(JobInstanceNames::RESUBSCRIBE_PRODUCTS, 'franklin_insights', $output, JobInstanceNames::RESUBSCRIBE_PRODUCTS);
        }

        if (!$this->isJobInstanceAlreadyCreated(JobInstanceNames::IDENTIFY_PRODUCTS_TO_RESUBSCRIBE)) {
            $this->createJobInstance(JobInstanceNames::IDENTIFY_PRODUCTS_TO_RESUBSCRIBE, 'franklin_insights', $output, JobInstanceNames::IDENTIFY_PRODUCTS_TO_RESUBSCRIBE);
        }

        if (!$this->isJobInstanceAlreadyCreated(JobInstanceNames::SYNCHRONIZE)) {
            $this->createJobInstance(JobInstanceNames::SYNCHRONIZE, 'franklin_insights', $output, JobInstanceNames::SYNCHRONIZE);
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

    private function createJobInstance(string $jobName, string $jobType, OutputInterface $output, string $label): void
    {
        $query = <<<SQL
INSERT INTO `akeneo_batch_job_instance` (`code`, `label`, `job_name`, `status`, `connector`, `raw_parameters`, `type`)
VALUES (
    :job_name,
    :job_label,
    :job_name,
    0,
    'Franklin Insights Connector',
    'a:0:{}',
    ':job_type'
);
SQL;
        $this->db->executeUpdate(
            $query,
            [
                'job_name' => $jobName,
                'job_type' => $jobType,
                'job_label' => $label,
            ],
            [
                'job_name' => \PDO::PARAM_STR,
                'job_type' => \PDO::PARAM_STR,
                'job_label' => \PDO::PARAM_STR,
            ]
        );
    }
}
