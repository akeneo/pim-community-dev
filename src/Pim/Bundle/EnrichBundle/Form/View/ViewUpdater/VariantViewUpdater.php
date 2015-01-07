<?php

namespace Pim\Bundle\EnrichBundle\Form\View\ViewUpdater;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * View updater for variant groups
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantViewUpdater implements ViewUpdaterInterface
{
    /** @var PropertyAccessorInterface */
    protected $propertyAccessor;

    /**
     * @param PropertyAccessorInterface $propertyAccessor
     */
    public function __construct(PropertyAccessorInterface $propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;
    }

    /**
     * {@inheritdoc}
     */
    public function update($view)
    {
        if (isset($view['value'])) {
            $valueFormViews = [$view['value']];
        } elseif (isset($view['values'])) {
            $valueFormViews = $view['values'];
        } else {
            throw new \LogicException('A product form view should be passed with at least one value');
        }

        foreach ($valueFormViews as $valueFormView) {
            try {
                $productValue = $this->propertyAccessor->getValue($valueFormView, 'vars[value]');
            } catch (NoSuchPropertyException $e) {
                throw new \LogicException($e->getMessage(), $e->getCode(), $e);
            }

            if ($this->isUpdatedByVariant($productValue)) {
                $this->markAttributeAsUpdatedByVariant($valueFormView, $productValue);
            }
        }
    }

    /**
     * Check if an attribute is updated by a variant group or not
     *
     * @param ProductValueInterface $productValue
     */
    protected function isUpdatedByVariant(ProductValueInterface $productValue)
    {
        /** @var ProductInterface $product */
        $product = $productValue->getEntity();

        //In case of mass edit the product is null
        if (null === $product) {
            return false;
        }

        $groups = $product->getGroups();
        $variantGroup = null;
        /** @var GroupInterface $group */
        foreach ($groups as $group) {
            if ($group->getType()->isVariant()) {
                // TODO : will have only one after PIM-2448, add short cut getVariantGroup() ?
                $variantGroup = $group;
            }
        }

        if ($variantGroup) {
            $template = $variantGroup->getProductTemplate();

            return ($template) ? $template->hasValueForAttribute($productValue->getAttribute()) : false;
        }

        return false;
    }

    /**
     * Mark attribute as variant
     *
     * @param FormView              $view
     * @param ProductValueInterface $value
     */
    protected function markAttributeAsUpdatedByVariant(FormView $view, ProductValueInterface $value)
    {
        // TODO : will have only one after PIM-2448, add shortcut getVariantGroup() ?
        foreach ($value->getEntity()->getGroups() as $group) {
            if ($group->getType()->isVariant()) {
                $view->vars['from_variant'] = $group;
            }
        }

        $view->vars['disabled']  = true;
        $view->vars['read_only'] = true;

        foreach ($view as $child) {
            $this->markAttributeAsUpdatedByVariant($child, $value);
        }
    }
}
