<?php

namespace Pim\Bundle\EnrichBundle\Form\View\ViewUpdater;

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
    public function update(array $view)
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
     *
     * @return bool
     */
    protected function isUpdatedByVariant(ProductValueInterface $productValue)
    {
        $product = $productValue->getEntity();

        //In case of mass edit the product is null
        if (null === $product) {
            return false;
        }

        $variantGroup = $product->getVariantGroup();
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
        $view->vars['from_variant'] = $value->getEntity()->getVariantGroup();
        $view->vars['disabled']  = true;
        $view->vars['read_only'] = true;

        foreach ($view as $child) {
            $this->markAttributeAsUpdatedByVariant($child, $value);
        }
    }
}
