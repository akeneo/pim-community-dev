<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Symfony\Component\Form\FormFactoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * The attribute type interface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * Get the value form type name to use to ensure binding
     *
     * @param ProductValueInterface $value
     *
     * @return string
     */
    public function prepareValueFormName(ProductValueInterface $value);

    /**
     * Get value form type alias to use to render value
     *
     * @param ProductValueInterface $value
     *
     * @return string
     */
    public function prepareValueFormAlias(ProductValueInterface $value);

    /**
     * Get value form type options to configure the form
     *
     * @param ProductValueInterface $value
     *
     * @return array
     */
    public function prepareValueFormOptions(ProductValueInterface $value);

    /**
     * Guess the constraints to apply on the form
     *
     * @param ProductValueInterface $value
     *
     * @return array
     */
    public function prepareValueFormConstraints(ProductValueInterface $value);

    /**
     * Get value form type data
     *
     * @param ProductValueInterface $value
     *
     * @return mixed
     */
    public function prepareValueFormData(ProductValueInterface $value);

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
