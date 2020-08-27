<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Exception\AnotherJobStillRunningException;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobLauncher\RunUniqueProcessJob;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Connector\JobParameters\PrepareEvaluationsParameters;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlag;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PrepareEvaluationsCommand extends Command
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

        $this->runUniqueProcessJob = $runUniqueProcessJob;
        $this->featureFlag = $featureFlag;
    }

    protected function configure()
    {
        $this
            ->setName('pim:data-quality-insights:prepare-evaluations')
            ->setDescription('Prepare the evaluations of products and structure');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (! $this->featureFlag->isEnabled()) {
            $output->writeln('Data Quality Insights feature is disabled');
            return;
        }

        try {
            $this->runUniqueProcessJob->run('data_quality_insights_prepare_evaluations', function (?JobExecution $lastJobExecution) {
                $defaultFrom = new \DateTime(PrepareEvaluationsParameters::UPDATED_SINCE_DEFAULT_TIME);

                $from = $defaultFrom;
                if (null !== $lastJobExecution) {
                    $from = max($lastJobExecution->getStartTime(), $defaultFrom);
                }

                return [PrepareEvaluationsParameters::UPDATED_SINCE_PARAMETER => $from->format(PrepareEvaluationsParameters::UPDATED_SINCE_DATE_FORMAT)];
            });
        } catch (AnotherJobStillRunningException $e) {
            exit(0);
        }
    }
}
