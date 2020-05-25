<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleApplier;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Tool\Component\RuleEngine\ActionApplier\ActionApplierRegistryInterface;

/**
 * Updates products when apply a rule
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ProductsUpdater
{
    /** @var ActionApplierRegistryInterface */
    protected $applierRegistry;

    /**
     * @param ActionApplierRegistryInterface  $applierRegistry
     */
    public function __construct(
        ActionApplierRegistryInterface $applierRegistry
    ) {
        $this->applierRegistry = $applierRegistry;
    }

    /**
     * @param RuleInterface      $rule
     * @param ProductInterface[] $products
     */
    public function update(RuleInterface $rule, array $products): array
    {
        return $this->updateFromRule($products, $rule);
    }

    /**
     * @param ProductInterface[] $products
     * @param RuleInterface      $rule
     */
    protected function updateFromRule(array $products, RuleInterface $rule): array
    {
        $updatedEntities = [];
        $actions = $rule->getActions();
        foreach ($actions as $action) {
            $updatedByAction = $this->applierRegistry->getActionApplier($action)->applyAction($action, $products);
            foreach ($updatedByAction as $entity) {
                $updatedEntities[$this->getEntityId($entity)] = $entity;
            }
        }

        return $updatedEntities;
    }

    private function getEntityId(EntityWithValuesInterface $entity): string
    {
        return sprintf(
            '%s_%d',
            $entity instanceof ProductModelInterface ? 'product_model' : 'product',
            $entity->getId()
        );
    }
}
