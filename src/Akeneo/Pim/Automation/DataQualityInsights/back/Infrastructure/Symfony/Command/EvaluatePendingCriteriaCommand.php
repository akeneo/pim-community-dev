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

    public function __construct(CreateProductsCriteriaEvaluations $createProductsCriteriaEvaluations, EvaluatePendingCriteria $evaluatePendingCriteria)
    {
        $this->createProductsCriteriaEvaluations = $createProductsCriteriaEvaluations;
        $this->evaluatePendingCriteria = $evaluatePendingCriteria;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('pimee:data-quality-insights:evaluate-pending-criteria')
            ->setDescription('Launch the evaluation of all the pending criteria of a specific product id')
            ->addOption(
                'product',
                'p',
                InputOption::VALUE_REQUIRED,
                'Product id to evaluate',
                1
            )
            ->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $productId = intval($input->getOption('product'));

        $output->writeln('Start to evaluate pending criteria of following product: ' . $productId);

        $this->createProductsCriteriaEvaluations->create([new ProductId($productId)]);
        $this->evaluatePendingCriteria->execute([$productId]);

        $output->writeln('All pending criteria have been evaluated.');
    }
}
