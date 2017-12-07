<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\Connector\Executor;

use Akeneo\Bundle\RuleEngineBundle\Repository\RuleDefinitionRepositoryInterface;
use Akeneo\Bundle\RuleEngineBundle\Runner\RunnerInterface;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Execute all the rules to a set of products.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class RulesExecutor implements ItemWriterInterface
{
    /** @var RunnerInterface */
    protected $runner;

    /** @var RuleDefinitionRepositoryInterface */
    protected $ruleRepository;

    /**
     * @param RunnerInterface                   $runner
     * @param RuleDefinitionRepositoryInterface $ruleRepository
     */
    public function __construct(
        RunnerInterface $runner,
        RuleDefinitionRepositoryInterface $ruleRepository
    ) {
        $this->runner = $runner;
        $this->ruleRepository = $ruleRepository;
    }

    /**
     * @param ProductInterface[] $products
     *
     * {@inheritdoc}
     */
    public function write(array $products)
    {
        $productIds = $this->getProductIds($products);

        if (!empty($productIds)) {
            $ruleDefinitions = $this->ruleRepository->findAllOrderedByPriority();

            foreach ($ruleDefinitions as $ruleDefinition) {
                $this->runner->run($ruleDefinition, ['selected_products' => $productIds]);
            }
        }
    }

    /**
     * @param array $entitiesWithFamily
     *
     * @return string[]
     */
    private function getProductIds(array $entitiesWithFamily): array
    {
        $productIds = [];
        foreach ($entitiesWithFamily as $entityWithFamily) {
            if ($entityWithFamily instanceof ProductInterface &&
                null !== $entityWithFamily->getId()
            ) {
                $productIds[] = (string) $entityWithFamily->getId();
            }
        }

        return $productIds;
    }
}
