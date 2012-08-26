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
class SetType extends AbstractType
{
    private $_copySetOptions = array();
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
                'choices'       => $this->getCopySetOptions(),
                'required'      => true,
                'property_path' => false
            )
        );

        // set groups
        $builder->add(
            'groups', 'collection',
            array(
                'type'         => new GroupLinkType(),
                'by_reference' => false,
            )
        );

        // set attributes
        /*
        $builder->add(
            'attributes', 'collection',
            array(
                'type'         => new AttributeLinkType(),
                'by_reference' => false,
            )
        );*/

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
            $builder->get('others')->add('attribute_'.$attribute->getId(), new AttributeLinkType($attribute));
        }

    }

    /**
     * Return identifier
     * @see Symfony\Component\Form.FormTypeInterface::getName()
     */
    public function getName()
    {
        return 'strixos_catalog_attributeset';
    }

    /**
     * Return list of attribute sets
     * @return Array
     */
    public function setCopySetOptions($sets)
    {
        $this->_copySetOptions = $sets;
    }

    /**
     * Return list of attribute sets
     * @return Array
     */
    public function getCopySetOptions()
    {
        return $this->_copySetOptions;
    }

    /**
    * Return list of attributes
    * @return Array
    */
    public function setAvailableAttributeOptions($attributes)
    {
        $this->_availableAttributeOptions = $attributes;
    }

    /**
     * Return list of attributes
     * @return Array
     */
    public function getAvailableAttributeOptions()
    {
        return $this->_availableAttributeOptions;
    }

}