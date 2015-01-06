<?php

namespace Pim\Bundle\EnrichBundle\Form\View\ViewUpdater;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class VariantViewUpdater implements ViewUpdaterInterface
{
    protected $propertyAccessor;

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
        }

        foreach ($valueFormViews as $valueFormView) {
            try {
                $productValue = $this->propertyAccessor->getValue($valueFormView, 'vars[value]');
            } catch (NoSuchPropertyException $e) {
                throw $e;
            }

            if ($this->isUpdatedByVariant($productValue)) {
                $this->markAttributeAsUpdatedByVariant($valueFormView, $productValue);
            }
        }
    }

    /**
     * Check if an attribute is updated by a variant group or not
     *
     * @param array  $views
     */
    protected function isUpdatedByVariant(ProductValueInterface $productValue)
    {
        /** @var ProductInterface $product */
        $product = $productValue->getEntity();

        //In case of mass edit the product is null
        if (null === $product) {
            return false;
        }

        $groups  = $product->getGroups();
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
     * @param FormView $view
     */
    protected function markAttributeAsUpdatedByVariant(FormView $view, $value)
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
