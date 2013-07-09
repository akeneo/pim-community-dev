<?php

namespace Pim\Bundle\ProductBundle\Form\View;

use Symfony\Component\Form\FormView;
use Pim\Bundle\ProductBundle\Entity\ProductValue;
use Pim\Bundle\ProductBundle\Entity\AttributeGroup;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFormView
{
    protected $view = array();

    public function addChildren(ProductValue $value, FormView $view)
    {
        $attribute = $value->getAttribute();
        $group = $attribute->getVirtualGroup();

        if (!$this->hasGroup($group)) {
            $this->initializeGroup($group);
        }

        $this->addValue($value, $view);
    }

    private function hasGroup(AttributeGroup $group)
    {
        return isset($this->view[$group->getId()]);
    }

    private function initializeGroup(AttributeGroup $group)
    {
        $this->view[$group->getId()] = array(
            'name' => $group->getName(),
        );
    }

    private function addValue(ProductValue $value, FormView $view)
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
            $attributeView['classes'] = array(
                'scopable' => true,
            );
        } else {
            $attributeView['value'] = $view;
        }

        $this->view[$group->getId()]['attributes'] = array(
            $attribute->getId() => $attributeView,
        );
    }

    private function getAttributeValues(ProductAttribute $attribute)
    {
        $group = $attribute->getVirtualGroup();
        if (!isset($this->view[$group->getId()]['attributes'][$attribute->getId()]['values'])) {
            return array();
        }

        return $this->view[$group->getId()]['attributes'][$attribute->getId()]['values'];
    }

    public function getView()
    {
        return $this->view;
    }
}

