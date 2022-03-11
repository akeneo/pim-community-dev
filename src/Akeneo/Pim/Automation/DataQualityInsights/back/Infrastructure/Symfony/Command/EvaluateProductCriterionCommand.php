<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\back\Infrastructure\Symfony\Command;

use Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\EvaluateCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Write;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductIdCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class EvaluateProductCriterionCommand extends Command
{
    protected static $defaultName = 'pim:data-quality-insights:evaluate-criteria';
    protected static $defaultDescription = 'Evaluate quality criteria for product or product model';

    public function __construct(
        private EvaluateCriteria $evaluateProductCriteria,
        private EvaluateCriteria $evaluateProductModelCriteria,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHidden(true);
        $this->addArgument('identifiers', InputArgument::IS_ARRAY | InputArgument::REQUIRED, 'List of product identifiers');
        $this->addOption('type', 't', InputOption::VALUE_REQUIRED, '[product] or [product_model]', 'product');
        $this->addOption('criteria', 'c', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Criteria codes to evaluate');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title($this::$defaultDescription);

        $identifiers = $input->getArgument('identifiers');
        $type = $input->getOption('type');
        $criteria = $input->getOption('criteria');

        $evaluateCriteria = $type === 'product_model' ? $this->evaluateProductModelCriteria : $this->evaluateProductCriteria;

        $evaluateCriteria(ProductIdCollection::fromStrings($identifiers), $criteria,
            function (Write\CriterionEvaluation $criterionEvaluation, ProductValuesCollection $productValues, float $startTime, float $endTime, float $duration) use ($io, $type) {
                $io->info(sprintf('Product [%s] with id %d, criterion [%s], %fs', $type, $criterionEvaluation->getProductId()->toInt(), (string) $criterionEvaluation->getCriterionCode(), $duration));
            }
        );

        return Command::SUCCESS;
    }
}
