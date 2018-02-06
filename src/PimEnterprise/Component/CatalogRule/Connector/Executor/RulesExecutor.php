<?php

declare(strict_types=1);

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
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;

/**
 * Execute all the rules to a set of entities with family.
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
     * @param EntityWithValuesInterface[] $entitiesWithValues
     *
     * {@inheritdoc}
     */
    public function write(array $entitiesWithValues): void
    {
        $entityIds = $this->getEntityIds($entitiesWithValues);

        if (!empty($entityIds['selected_products']) || !empty($entityIds['selected_product_models'])) {
            $ruleDefinitions = $this->ruleRepository->findAllOrderedByPriority();

            foreach ($ruleDefinitions as $ruleDefinition) {
                $this->runner->run($ruleDefinition, $entityIds);
            }
        }
    }

    /**
     * @param EntityWithValuesInterface[] $entitiesWithValues
     *
     * @return string[]
     */
    private function getEntityIds(array $entitiesWithValues): array
    {
        $entityIds = [
            'selected_products' => [],
            'selected_product_models' => [],
        ];

        foreach ($entitiesWithValues as $entityWithValues) {
            if ($entityWithValues instanceof ProductInterface &&
                null !== $entityWithValues->getId()
            ) {
                $entityIds['selected_products'][] = sprintf(
                    '%s%d',
                    'product_',
                    $entityWithValues->getId()
                );
            } elseif ($entityWithValues instanceof ProductModelInterface &&
                null !== $entityWithValues->getId()
            ) {
                $entityIds['selected_product_models'][] = sprintf(
                    '%s%d',
                    'product_model_',
                    $entityWithValues->getId()
                );
            }
        }

        return $entityIds;
    }
}
