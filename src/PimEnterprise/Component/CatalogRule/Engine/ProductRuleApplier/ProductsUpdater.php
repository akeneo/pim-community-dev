<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\CatalogRule\Engine\ProductRuleApplier;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Component\RuleEngine\ActionApplier\ActionApplierRegistryInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Updater\ProductTemplateUpdaterInterface;

/**
 * Updates products when apply a rule
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ProductsUpdater
{
    /** @var ActionApplierRegistryInterface */
    protected $applierRegistry;

    /** @var ProductTemplateUpdaterInterface */
    protected $templateUpdater;

    /**
     * @param ActionApplierRegistryInterface  $applierRegistry
     * @param ProductTemplateUpdaterInterface $templateUpdater
     */
    public function __construct(
        ActionApplierRegistryInterface $applierRegistry,
        ProductTemplateUpdaterInterface $templateUpdater
    ) {
        $this->applierRegistry = $applierRegistry;
        $this->templateUpdater = $templateUpdater;
    }

    /**
     * @param RuleInterface      $rule
     * @param ProductInterface[] $products
     */
    public function update(RuleInterface $rule, array $products)
    {
        $this->updateFromRule($products, $rule);
    }

    /**
     * @param ProductInterface[] $products
     * @param RuleInterface      $rule
     */
    protected function updateFromRule(array $products, RuleInterface $rule)
    {
        $actions = $rule->getActions();
        foreach ($actions as $action) {
            $this->applierRegistry->getActionApplier($action)->applyAction($action, $products);
        }
    }
}
