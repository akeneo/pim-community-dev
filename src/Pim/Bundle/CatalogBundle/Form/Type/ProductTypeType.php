<?php
namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 *
 * @author     Romain @ Akeneo
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductTypeType extends AbstractType
{
    private $_copyTypeOptions = array();
    private $_availableAttributeOptions = array();

    /**
     * (non-PHPdoc)
     * @see Symfony\Component\Form.AbstractType::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $options['data'];

        $builder->add('id', 'hidden');

        $builder->add(
            'code', null, array(
                'disabled'  => ($entity->getId())? true : false
            )
        );

        $builder->add(
            'copyfromset', 'choice', array(
                'choices'       => $this->getCopyTypeOptions(),
                'required'      => true,
                'property_path' => false
            )
        );
/*
        // set groups
        $builder->add(
            'groups', 'collection',
            array(
                'type'         => new GroupLinkType(),
                'by_reference' => false,
            )
        );

        // available attributes (not related to current set)
        $builder->add(
            'others', 'collection',
            array(
                'type'          => new AttributeLinkType(),
                'property_path' => false
            )
        );
        // add attributes
        foreach ($this->getAvailableAttributeOptions() as $attribute) {
            $builder->get('others')->add('attribute_'.$attribute, new AttributeLinkType($attribute));
        }
*/
    }

    /**
     * Return identifier
     * @see Symfony\Component\Form.FormTypeInterface::getName()
     */
    public function getName()
    {
        return 'akeneo_catalog_producttype';
    }

    /**
     * Return list of $types
     * @return Array
     */
    public function setCopyTypeOptions($types)
    {
        $this->_copyTypeOptions = $types;
    }

    /**
     * Return list of type
     * @return Array
     */
    public function getCopyTypeOptions()
    {
        return $this->_copyTypeOptions;
    }

    /**
    * Return list of attributes
    * @return Array
    *
    public function setAvailableAttributeOptions($attributes)
    {
        $this->_availableAttributeOptions = $attributes;
    }*/

    /**
     * Return list of attributes
     * @return Array
     *
    public function getAvailableAttributeOptions()
    {
        return $this->_availableAttributeOptions;
    }*/

}