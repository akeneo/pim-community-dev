<?php

namespace Pim\Bundle\EnrichBundle\Form\View;

use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Model\AttributeGroupInterface;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\EnrichBundle\Form\View\ViewUpdater\ViewUpdaterRegistry;
use Symfony\Component\Form\FormView;

/**
 * Custom form view for Product form
 * This class allows to group ProductValue fields in order to use them easily in the templates
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductFormView implements ProductFormViewInterface
{
    /**
     * A list of the attribute types for which creating a new option is allowed
     *
     * @var array
     */
    protected $choiceAttributeTypes = array(
        AttributeTypes::OPTION_MULTI_SELECT,
        AttributeTypes::OPTION_SIMPLE_SELECT
    );

    /** @var FormView|array */
    protected $view = [];

    /** @var ViewUpdaterRegistry */
    protected $viewUpdaterRegistry;

    /**
     * @param ViewUpdaterRegistry $viewUpdaterRegistry
     */
    public function __construct(ViewUpdaterRegistry $viewUpdaterRegistry)
    {
        $this->viewUpdaterRegistry = $viewUpdaterRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function getView()
    {
        foreach ($this->view as $group) {
            foreach ($group['attributes'] as $attributeView) {
                $this->updateView($attributeView);
            }
        }

        return $this->view;
    }

    /**
     * {@inheritdoc}
     */
    public function addChildren(ProductValueInterface $value, FormView $view)
    {
        $attribute = $value->getAttribute();
        $group = $attribute->getGroup();

        if (!$this->hasGroup($group)) {
            $this->initializeGroup($group);
        }

        $this->addValue($value, $view);

        $this->orderGroupAttributes($group);
    }

    /**
     * @param AttributeGroupInterface $group
     */
    protected function orderGroupAttributes(AttributeGroupInterface $group)
    {
        $this->view[$group->getId()]['attributes'] = $this->sortAttributes($this->view[$group->getId()]['attributes']);
    }

    /**
     * @param AttributeGroupInterface $group
     *
     * @return bool
     */
    protected function hasGroup(AttributeGroupInterface $group)
    {
        return isset($this->view[$group->getId()]);
    }

    /**
     * @param AttributeGroupInterface $group
     */
    protected function initializeGroup(AttributeGroupInterface $group)
    {
        $this->view[$group->getId()] = array(
            'label'      => $group->getLabel(),
            'attributes' => array()
        );
    }

    /**
     * @param AttributeInterface $attribute
     *
     * @return array
     */
    protected function getAttributeClasses(AttributeInterface $attribute)
    {
        $classes = array();
        if ($attribute->isScopable()) {
            $classes['scopable'] = true;
        }

        if ($attribute->isLocalizable()) {
            $classes['localizable'] = true;
        }

        if (AttributeTypes::PRICE_COLLECTION === $attribute->getAttributeType()) {
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
        $attribute     = $value->getAttribute();
        $attributeView = $this->prepareAttributeView($attribute, $value, $view);
        $group         = $attribute->getGroup();

        $attributeKey = $attribute->getCode();
        if ($value->getLocale()) {
            $attributeKey .= '_' . $value->getLocale();
        }
        $this->view[$group->getId()]['attributes'][$attributeKey] = $attributeView;
    }

    /**
     * Prepare attribute view
     *
     * @param AttributeInterface    $attribute
     * @param ProductValueInterface $value
     * @param FormView              $view
     *
     * @return array
     */
    protected function prepareAttributeView(AttributeInterface $attribute, ProductValueInterface $value, FormView $view)
    {
        $attributeView = array(
            'id'                 => $attribute->getId(),
            'isRemovable'        => $value->isRemovable(),
            'code'               => $attribute->getCode(),
            'label'              => $attribute->getLabel(),
            'sortOrder'          => $attribute->getSortOrder(),
            'allowValueCreation' => in_array($attribute->getAttributeType(), $this->choiceAttributeTypes),
            'locale'             => $value->getLocale(),
        );

        if ($attribute->isScopable()) {
            $attributeView['values'] = array_merge(
                $this->getAttributeValues($attribute, $value->getLocale()),
                array($value->getScope() => $view)
            );
            ksort($attributeView['values']);
        } else {
            $attributeView['value'] = $view;
        }

        $classes = $this->getAttributeClasses($attribute);
        if (!empty($classes)) {
            $attributeView['classes'] = $classes;
        }

        return $attributeView;
    }

    /**
     * Update the current view with all view updaters
     *
     * @param array $view
     */
    protected function updateView(array $view)
    {
        $viewUpdaters = $this->viewUpdaterRegistry->getUpdaters();

        foreach ($viewUpdaters as $viewUpdater) {
            $viewUpdater->update($view);
        }
    }

    /**
     * @param AttributeInterface $attribute
     * @param string             $locale
     *
     * @return ArrayCollection
     */
    protected function getAttributeValues(AttributeInterface $attribute, $locale)
    {
        $group = $attribute->getGroup();
        $key = $attribute->getCode();
        if ($locale) {
            $key .= '_' . $locale;
        }
        if (!isset($this->view[$group->getId()]['attributes'][$key]['values'])) {
            return array();
        }

        return $this->view[$group->getId()]['attributes'][$key]['values'];
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
