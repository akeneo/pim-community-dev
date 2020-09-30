<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\AnotherJobStillRunningException;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobLauncher\RunUniqueProcessJob;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters\Evaluation;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PimEnterpriseLaunchEvaluationsCommand extends Command
{
    /** @var FeatureFlag */
    private $featureFlag;

    /** @var RunUniqueProcessJob */
    private $runUniqueProcessJob;

    public function __construct(
        RunUniqueProcessJob $runUniqueProcessJob,
        FeatureFlag $featureFlag
    ) {
        parent::__construct();

        $this->featureFlag = $featureFlag;
        $this->runUniqueProcessJob = $runUniqueProcessJob;
    }

    protected function configure()
    {
        $this
            ->setName('pim:data-quality-insights:evaluations')
            ->setDescription('Launch the evaluations of products and structure');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (! $this->featureFlag->isEnabled()) {
            $output->writeln('<info>Data Quality Insights feature is disabled</info>');
            return;
        }

        try {
            $this->runUniqueProcessJob->run('data_quality_insights_evaluations', function (?JobExecution $lastJobExecution) {
                return $this->getJobParameters($lastJobExecution);
            });
        } catch (AnotherJobStillRunningException $e) {
            exit(0);
        }
    }

    private function getJobParameters(?JobExecution $lastJobExecution): array
    {
        $defaultEvaluationDate = new \DateTime(Evaluation::EVALUATED_SINCE_DEFAULT_TIME);

        $lastEvaluationDate = $defaultEvaluationDate;
        if (null !== $lastJobExecution) {
            $lastEvaluationDate = max($lastJobExecution->getStartTime(), $defaultEvaluationDate);
        }

        return [
            Evaluation::EVALUATED_SINCE_PARAMETER => $lastEvaluationDate->format(Evaluation::EVALUATED_SINCE_DATE_FORMAT)
        ];
    }
}
