<?php

namespace Pim\Bundle\CatalogBundle\Form\View;

use Symfony\Component\Form\FormView;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;

/**
 * Custom form view for Product form
 *
 * @TODO : develop the goal of this class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @TODO : Set all method scopes to protected
 */
class ProductFormView
{
    protected $view = array();

    /**
     * @return FormView
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * @param ProductValueInterface $value
     * @param FormView              $view
     */
    public function addChildren(ProductValueInterface $value, FormView $view)
    {
        $attribute = $value->getAttribute();
        $group = $attribute->getVirtualGroup();

        if (!$this->hasGroup($group)) {
            $this->initializeGroup($group);
        }

        $this->addValue($value, $view);

        $this->orderGroupAttributes($group);
    }

    /**
     * @param AttributeGroup $group
     */
    private function orderGroupAttributes(AttributeGroup $group)
    {
        $this->view[$group->getId()]['attributes'] = $this->sortAttributes($this->view[$group->getId()]['attributes']);
    }

    /**
     * @param AttributeGroup $group
     *
     * @return boolean
     */
    private function hasGroup(AttributeGroup $group)
    {
        return isset($this->view[$group->getId()]);
    }

    /**
     * @param AttributeGroup $group
     */
    private function initializeGroup(AttributeGroup $group)
    {
        $this->view[$group->getId()] = array(
            'name'       => $group->getName(),
            'attributes' => array(),
        );
    }

    /**
     * @param ProductAttribute $attribute
     *
     * @return array
     *
     * @TODO : set protected ?! What happened if another type is added and need some specific redefinition
     */
    private function getAttributeClasses(ProductAttribute $attribute)
    {
        $classes = array();
        if ($attribute->getScopable()) {
            $classes['scopable'] = true;
        }

        if ($attribute->getTranslatable()) {
            $classes['translatable'] = true;
        }

        if ('pim_product_price_collection' === $attribute->getAttributeType()) {
            $classes['currency'] = true;
        }

        return $classes;
    }

    /**
     * @param ProductValueInterface $value
     * @param FormView              $view
     */
    private function addValue(ProductValueInterface $value, FormView $view)
    {
        $attribute = $value->getAttribute();
        $group     = $attribute->getVirtualGroup();

        $attributeView = array(
            'isRemovable' => $value->isRemovable(),
            'code'        => $attribute->getCode(),
            'label'       => $attribute->getLabel(),
            'sortOrder'   => $attribute->getSortOrder(),
        );

        if ($attribute->getScopable()) {
            $attributeView['values'] = array_merge(
                $this->getAttributeValues($attribute),
                array($value->getScope() => $view)
            );
        } else {
            $attributeView['value'] = $view;
        }

        $classes = $this->getAttributeClasses($attribute);
        if (!empty($classes)) {
            $attributeView['classes'] = $classes;
        }

        $this->view[$group->getId()]['attributes'][$attribute->getId()] = $attributeView;
    }

    /**
     * @param ProductAttribute $attribute
     *
     * @return ArrayCollection
     */
    private function getAttributeValues(ProductAttribute $attribute)
    {
        $group = $attribute->getVirtualGroup();
        if (!isset($this->view[$group->getId()]['attributes'][$attribute->getId()]['values'])) {
            return array();
        }

        return $this->view[$group->getId()]['attributes'][$attribute->getId()]['values'];
    }

    /**
     * Sort an array of by the values of its sortOrder key
     *
     * @param array $attributes
     *
     * @return array
     */
    protected function sortAttributes(array $attributes)
    {
        uasort(
            $attributes,
            function ($first, $second) {
                if ($first['sortOrder'] === $second['sortOrder']) {
                    return 0;
                }

                return $first['sortOrder'] > $second['sortOrder'] ? 1 : -1;
            }
        );

        return $attributes;
    }
}
