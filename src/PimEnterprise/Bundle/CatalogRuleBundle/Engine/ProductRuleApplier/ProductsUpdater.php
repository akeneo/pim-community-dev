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
use Akeneo\Component\StorageUtils\Updater\PropertyCopierInterface;
use Akeneo\Component\StorageUtils\Updater\PropertySetterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Component\Catalog\Updater\ProductTemplateUpdaterInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductCopyValueActionInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Model\ProductSetValueActionInterface;

/**
 * Updates products when apply a rule
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class ProductsUpdater
{
    /** @var PropertySetterInterface */
    protected $propertySetter;

    /** @var PropertyCopierInterface */
    protected $propertyCopier;

    /** @var ProductTemplateUpdaterInterface */
    protected $templateUpdater;

    /**
     * @param PropertySetterInterface         $propertySetter
     * @param PropertyCopierInterface         $propertyCopier,
     * @param ProductTemplateUpdaterInterface $templateUpdater
     */
    public function __construct(
        PropertySetterInterface $propertySetter,
        PropertyCopierInterface $propertyCopier,
        ProductTemplateUpdaterInterface $templateUpdater
    ) {
        $this->propertySetter  = $propertySetter;
        $this->propertyCopier  = $propertyCopier;
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
        foreach ($products as $product) {
            $this->propertyCopier->copyData(
                $product,
                $product,
                $action->getFromField(),
                $action->getToField(),
                [
                    'from_locale' => $action->getFromLocale(),
                    'from_scope' => $action->getFromScope(),
                    'to_locale' => $action->getToLocale(),
                    'to_scope' => $action->getToScope()
                ]
            );
        }

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
        foreach ($products as $product) {
            $this->propertySetter->setData(
                $product,
                $action->getField(),
                $action->getValue(),
                ['locale' => $action->getLocale(), 'scope' => $action->getScope()]
            );
        }

        return $this;
    }
}
