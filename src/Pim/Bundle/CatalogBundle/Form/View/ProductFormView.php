<?php

namespace Pim\Bundle\CatalogBundle\Form\View;

use Symfony\Component\Form\FormView;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;

/**
 * Custom form view for Product form
 * This class allows to group ProductValue fields in order to use them easily in the templates
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFormView
{
    /**
     * @var array A list of the attribute types for which creating a new option is allowed
     */
    private $choiceAttributeTypes = array(
        'pim_catalog_multiselect',
        'pim_catalog_simpleselect'
    );

    /**
     * @var FormView|array
     */
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
    protected function orderGroupAttributes(AttributeGroup $group)
    {
        $this->view[$group->getId()]['attributes'] = $this->sortAttributes($this->view[$group->getId()]['attributes']);
    }

    /**
     * @param AttributeGroup $group
     *
     * @return boolean
     */
    protected function hasGroup(AttributeGroup $group)
    {
        return isset($this->view[$group->getId()]);
    }

    /**
     * @param AttributeGroup $group
     */
    protected function initializeGroup(AttributeGroup $group)
    {
        $this->view[$group->getId()] = array(
            'label'      => $group->getLabel(),
            'attributes' => array(),
        );
    }

    /**
     * @param ProductAttribute $attribute
     *
     * @return array
     */
    protected function getAttributeClasses(ProductAttribute $attribute)
    {
        $classes = array();
        if ($attribute->isScopable()) {
            $classes['scopable'] = true;
        }

        if ($attribute->getTranslatable()) {
            $classes['translatable'] = true;
        }

        if ('pim_catalog_price_collection' === $attribute->getAttributeType()) {
            $classes['currency'] = true;
        }

        return $classes;
    }

    /**
     * @param ProductValueInterface $value
     * @param FormView              $view
     */
    protected function addValue(ProductValueInterface $value, FormView $view)
    {
        $attribute = $value->getAttribute();
        $group     = $attribute->getVirtualGroup();

        $attributeView = array(
            'isRemovable'        => $value->isRemovable(),
            'code'               => $attribute->getCode(),
            'label'              => $attribute->getLabel(),
            'sortOrder'          => $attribute->getSortOrder(),
            'allowValueCreation' => in_array($attribute->getAttributeType(), $this->choiceAttributeTypes)
        );

        if ($attribute->isScopable()) {
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
    protected function getAttributeValues(ProductAttribute $attribute)
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
