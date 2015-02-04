<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleApplier;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductTemplateUpdaterInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;

/**
 * Updates products when apply a rule
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ProductsUpdater
{
    /** @var ProductUpdaterInterface */
    protected $productUpdater;

    /** @var ProductTemplateUpdaterInterface */
    protected $templateUpdater;

    /**
     * @param ProductUpdaterInterface         $productUpdater
     * @param ProductTemplateUpdaterInterface $templateUpdater
     */
    public function __construct(
        ProductUpdaterInterface $productUpdater,
        ProductTemplateUpdaterInterface $templateUpdater
    ) {
        $this->productUpdater  = $productUpdater;
        $this->templateUpdater = $templateUpdater;
    }

    /**
     * @param RuleInterface      $rule
     * @param ProductInterface[] $products
     */
    public function update(RuleInterface $rule, array $products)
    {
        $this->updateFromRule($products, $rule);
        $this->updateFromVariantGroup($products);
    }

    /**
     * @param ProductInterface[] $products
     * @param RuleInterface      $rule
     */
    public function updateFromRule(array $products, RuleInterface $rule)
    {
        $actions = $rule->getActions();
        foreach ($actions as $action) {
            if ($action instanceof ProductSetValueActionInterface) {
                $this->applySetAction($products, $action);
            } elseif ($action instanceof ProductCopyValueActionInterface) {
                $this->applyCopyAction($products, $action);
            } else {
                throw new \LogicException(
                    sprintf('The action "%s" is not supported yet.', ClassUtils::getClass($action))
                );
            }
        }
    }

    /**
     * @param ProductInterface[] $products
     */
    public function updateFromVariantGroup(array $products)
    {
        foreach ($products as $product) {
            $variantGroup = $product->getVariantGroup();
            $template = $variantGroup !== null ? $variantGroup->getProductTemplate() : null;
            if (null !== $template) {
                $this->templateUpdater->update($template, [$product]);
            }
        }
    }

    /**
     * Apply a copy action on a subject set.
     *
     * @param ProductInterface[]              $products
     * @param ProductCopyValueActionInterface $action
     *
     * @return ProductRuleApplier
     */
    protected function applyCopyAction(array $products, ProductCopyValueActionInterface $action)
    {
        $this->productUpdater->copyValue(
            $products,
            $action->getFromField(),
            $action->getToField(),
            $action->getFromLocale(),
            $action->getToLocale(),
            $action->getFromScope(),
            $action->getToScope()
        );

        return $this;
    }

    /**
     * Applies a set action on a subject set.
     *
     * @param ProductInterface[]             $products
     * @param ProductSetValueActionInterface $action
     *
     * @return ProductRuleApplier
     */
    protected function applySetAction(array $products, ProductSetValueActionInterface $action)
    {
        $this->productUpdater->setValue(
            $products,
            $action->getField(),
            $action->getValue(),
            $action->getLocale(),
            $action->getScope()
        );

        return $this;
    }
}
