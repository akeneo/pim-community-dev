<?php
namespace Strixos\CatalogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Strixos\CatalogBundle\Entity\Attribute;

/**
 *
 * @author     Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductType extends AbstractType
{
    private $_attributeSet;

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Form.AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $product = $options['data'];

        // TODO drive from type and not add if in twig template ?
        $builder->add('id', 'hidden');
        $builder->add('sku');

        // set groups
        $builder->add(
            'groups', 'collection',
            array(
                'type'         => new GroupLinkType(),
                'by_reference' => false,
                'property_path' => false
            )
        );

        // add group with attribute and value pairs
        foreach ($this->getAttributeSet()->getGroups() as $group) {
            $attributeToValue = array();
            foreach ($group->getAttributes() as $attribute) {
                $attributeToValue[$attribute->getId()]= array(
                    'attribute' => $attribute,
                    'value'     => $product->getValue($attribute->getCode())
                );
            }
            $builder->get('groups')->add('group_'.$group->getId(), new GroupLinkType($group, $attributeToValue));
        }
    }

    /**
     * Return identifier
     * @see Symfony\Component\Form.FormTypeInterface::getName()
     */
    public function getName()
    {
        return 'strixos_catalog_product';
    }

    /**
     * Set product attribute set
     * @return ProductType
     */
    public function setAttributeSet($set)
    {
        $this->_attributeSet = $set;
        return $this;
    }

    /**
     * Return attribute set
     * @return mixed
     */
    public function getAttributeSet()
    {
        return $this->_attributeSet;
    }

}