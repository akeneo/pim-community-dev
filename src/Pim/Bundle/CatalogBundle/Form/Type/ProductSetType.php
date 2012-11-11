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
class ProductSetType extends AbstractType
{
    private $_copyTypeOptions = array();
    private $_availableFields = array();

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

        // create by copy
        $builder->add(
            'copyfromset', 'choice', array(
                'choices'       => $this->getCopyTypeOptions(),
                'required'      => false,
                'property_path' => false
            )
        );

        // set groups
        $builder->add(
            'groups', 'collection',
            array(
                'type'         => new ProductGroupType(),
                'by_reference' => true,
                'allow_add'    => true,
                'allow_delete' => true
            )
        );

        // available attributes (not related to current set)
        $builder->add(
            'others', 'collection',
            array(
                'type'          => new ProductGroupAttributeType(),
                'property_path' => false
            )
        );
        // add attributes
        foreach ($this->getAvailableFields() as $field) {
            $builder->get('others')->add('field_'.$field->getId(), new ProductGroupAttributeType($field));
        }
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
     * Return list of fields
     * @return Array
     *
     */
    public function setAvailableFields($fields)
    {
        $this->_availableFields = $fields;
    }

    /**
     * Return list of attributes
     * @return Array
     */
    public function getAvailableFields()
    {
        return $this->_availableFields;
    }

}