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

use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\CreateProductsCriteriaEvaluations;
use Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\EvaluatePendingCriteria;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Install\InitializeCriteriaEvaluation;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class EvaluatePendingCriteriaCommand extends Command
{
    /** @var CreateProductsCriteriaEvaluations */
    private $createProductsCriteriaEvaluations;

    /** @var EvaluatePendingCriteria */
    private $evaluatePendingCriteria;

    /** @var InitializeCriteriaEvaluation */
    private $initializeCriteriaEvaluation;

    public function __construct(
        CreateProductsCriteriaEvaluations $createProductsCriteriaEvaluations,
        EvaluatePendingCriteria $evaluatePendingCriteria,
        InitializeCriteriaEvaluation $initializeCriteriaEvaluation
    ) {
        $this->createProductsCriteriaEvaluations = $createProductsCriteriaEvaluations;
        $this->evaluatePendingCriteria = $evaluatePendingCriteria;
        $this->initializeCriteriaEvaluation = $initializeCriteriaEvaluation;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('pimee:data-quality-insights:evaluate-products')
            ->setDescription('Launch the evaluation of all the pending criteria of a specific product id')
            ->addOption(
                'product',
                'p',
                InputOption::VALUE_REQUIRED,
                'Product id to evaluate synchronously'
            )
            ->addOption(
                'full-catalog',
                'f',
                InputOption::VALUE_NONE,
                'Will initialize the full catalog evaluation asynchronously - Need to run the job daemon to evaluate',
            )
            ->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (null !== $input->getOption('product')) {
            $productId = intval($input->getOption('product'));

            $this->evaluateProduct($output, $productId);
        }

        if (true === $input->getOption('full-catalog')) {
            $this->scheduleFullCatalogEvaluation($output);
        }
    }

    private function evaluateProduct(OutputInterface $output, int $productId)
    {
        $output->writeln('Start to evaluate following product: ' . $productId);

        $this->createProductsCriteriaEvaluations->create([new ProductId($productId)]);
        $this->evaluatePendingCriteria->execute([$productId]);

        $output->writeln('Product is evaluated');
    }

    private function scheduleFullCatalogEvaluation(OutputInterface $output)
    {
        $output->writeln('Schedule the evaluation of all the catalog');
        $this->initializeCriteriaEvaluation->initialize();
        $output->writeln('Evaluation of all the catalog scheduled.');
    }
}
