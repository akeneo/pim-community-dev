<?php
namespace Oro\Bundle\FlexibleEntityBundle\AttributeType;

use Symfony\Component\Form\FormFactoryInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * The attribute type interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
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
