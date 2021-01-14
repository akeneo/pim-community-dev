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

use Akeneo\Pim\Automation\DataQualityInsights\Application\PurgeOutdatedData;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

/**
 * Command to launch manually a purge of the outdated data persisted for Data-Quality-Insights
 * It aims to be used as a replacement of the purge job. So the option date should be the date the job should have run.
 */
final class PurgeOutdatedDataCommand extends Command
{
    private const PURGE_CRITERION_EVALUATIONS = 'criterion-evaluations';
    private const PURGE_PRODUCT_AXIS_RATES = 'product-axis-rates';
    private const PURGE_DASHBOARD_PROJECTION_RATES = 'dashboard-projection-rates';

    /** @var PurgeOutdatedData */
    private $purgeOutdatedData;

    public function __construct(PurgeOutdatedData $purgeOutdatedData)
    {
        parent::__construct();

        $this->purgeOutdatedData = $purgeOutdatedData;
    }

    protected function configure()
    {
        $this
            ->setName('pimee:data-quality-insights:purge-outdated-data')
            ->setDescription('Purge the outdated data persisted for Data-Quality-Insights.')
            ->addArgument('type', InputArgument::OPTIONAL, sprintf('Type of data to purge (%s, %s, %s)',
                self::PURGE_CRITERION_EVALUATIONS,
                self::PURGE_PRODUCT_AXIS_RATES,
                self::PURGE_DASHBOARD_PROJECTION_RATES
            ))
            ->addOption('date', 'd', InputOption::VALUE_REQUIRED, 'Date from which the purge will be launched (Y-m-d)', date('Y-m-d'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $purgeDate = \DateTimeImmutable::createFromFormat('Y-m-d', $input->getOption('date'));
        $purgeType = $input->getArgument('type');

        if (!$purgeDate instanceof \DateTimeImmutable) {
            throw new \InvalidArgumentException(sprintf('The purge date "%s" is invalid.', $input->getOption('date')));
        }

        if ($purgeDate > new \DateTimeImmutable('now')) {
            throw new \InvalidArgumentException('The purge date cannot be in the future.');
        }

        if (!$this->confirmPurge($purgeType, $input, $output)) {
            return;
        }

        if (null === $purgeType) {
            $this->purgeCriterionEvaluations($output);
            $this->purgeProductAxisRates($purgeDate, $output);
            $this->purgeDashboardProjectionRates($purgeDate, $output);

            return;
        }

        switch ($purgeType) {
            case self::PURGE_CRITERION_EVALUATIONS:
                $this->purgeCriterionEvaluations($output);
                break;
            case self::PURGE_PRODUCT_AXIS_RATES:
                $this->purgeProductAxisRates($purgeDate, $output);
                break;
            case self::PURGE_DASHBOARD_PROJECTION_RATES:
                $this->purgeDashboardProjectionRates($purgeDate, $output);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Purge type "%s" does not exist.', $purgeType));
        }
    }

    private function purgeCriterionEvaluations(OutputInterface $output): void
    {
        $output->writeln('Start to purge criterion evaluations.');
        $this->purgeOutdatedData->purgeOutdatedCriterionEvaluations(-1);
        $output->writeln('Purge done.');
    }

    private function purgeProductAxisRates(\DateTimeImmutable $purgeDate, OutputInterface $output): void
    {
        $output->writeln('Start to purge product axis rates.');
        $this->purgeOutdatedData->purgeProductAxisRatesFrom($purgeDate);
        $output->writeln('Purge done.');
    }

    private function purgeDashboardProjectionRates(\DateTimeImmutable $purgeDate, OutputInterface $output)
    {
        $output->writeln('Start to purge dashboard projection rates.');
        $this->purgeOutdatedData->purgeDashboardProjectionRatesFrom($purgeDate);
        $output->writeln('Purge done.');
    }

    private function confirmPurge(?string $purgeType, InputInterface $input, OutputInterface $output): bool
    {
        $question = new ConfirmationQuestion(
            null === $purgeType ? 'Purge all the outdated data? [y/n]' : sprintf('Purge the "%s" outdated data? [y/n]', $purgeType),
            false
        );

        if (!$this->getHelper('question')->ask($input, $output, $question)) {
            $output->writeln('Purge aborted');

            return false;
        };

        return true;
    }
}
