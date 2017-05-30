<?php

namespace Pim\Bundle\CatalogBundle\AttributeType;

use Pim\Component\Catalog\AttributeTypeInterface as BaseAttributeTypeInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

/**
 * The attribute type interface
 *
 * TODO: ideally this interface should not be named AttributeTypeInterface but AttributeTypeFormInterface
 * TODO: and it should not extend Pim\Component\Catalog\AttributeTypeInterface
 * TODO: and maybe it should not be present here but in Enrich (to discuss).
 * TODO: we keep it as is, to avoid major BC breaks for all integrators that built a custom attribute type
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeTypeInterface extends BaseAttributeTypeInterface
{
    /**
     * Build form types for custom properties of an attribute
     *
     * @param FormFactoryInterface $factory   the form factory
     * @param AttributeInterface   $attribute the attribute
     *
     * @return FormInterface the form
     */
    public function buildAttributeFormTypes(FormFactoryInterface $factory, AttributeInterface $attribute);
}
