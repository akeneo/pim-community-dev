<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Consolidation\ConsolidateDashboardRates;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ConsolidationDate;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ConsolidateDashboardRatesCommand extends Command
{
    /** @var ConsolidateDashboardRates */
    private $consolidateDashboardRates;

    public function __construct(ConsolidateDashboardRates $consolidateDashboardRates)
    {
        parent::__construct();

        $this->consolidateDashboardRates = $consolidateDashboardRates;
    }

    protected function configure()
    {
        $this
            ->setName('pim:data-quality-insights:consolidate-dashboard-rates')
            ->setDescription('Consolidate the Data-Quality-Insights dashboard rates.')
            ->addArgument('day', InputArgument::OPTIONAL, 'Day of the consolidation "Y-m-d".', date('Y-m-d'));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consolidationDate = \DateTimeImmutable::createFromFormat('Y-m-d', $input->getArgument('day'));

        if (!$consolidationDate instanceof \DateTimeImmutable) {
            throw new \InvalidArgumentException(sprintf('The consolidation date "%s" is invalid', $input->getArgument('day')));
        }

        $consolidationDate = new ConsolidationDate($consolidationDate);

        $output->writeln(sprintf('Start to consolidate the dashboard rates for %s.', $consolidationDate->format('Y-m-d')));
        $this->consolidateDashboardRates->consolidate($consolidationDate);
        $output->writeln('Consolidation done.');

        return 0;
    }
}
