<?php

namespace Pim\Bundle\FlexibleEntityBundle\AttributeType;

use Symfony\Component\Form\FormFactoryInterface;
use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * The attribute type interface
 */
interface AttributeTypeInterface
{
    /**
     * Get name
     *
     * @return string
     */
    public function getName();

    /**
     * Build form type for flexible entity value
     *
     * @param FormFactoryInterface   $factory the form factory
     * @param FlexibleValueInterface $value   the flexible value
     *
     * @return FormInterface the form
     */
    public function buildValueFormType(FormFactoryInterface $factory, FlexibleValueInterface $value);

    /**
     * Build form types for custom properties of an attribute
     *
     * @param FormFactoryInterface $factory   the form factory
     * @param AbstractAttribute    $attribute the attribute
     *
     * @return FormInterface the form
     */
    public function buildAttributeFormTypes(FormFactoryInterface $factory, AbstractAttribute $attribute);
}
