<?php
namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 *
 * @author     Romain @ Akeneo
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductSetType extends AbstractType
{

    /**
     * @var string
     */
    protected $setClass;

    /**
     * @var string
     */
    protected $groupClass;


    /**
     * @var string
     */
    protected $attributeClass;

    /**
     * var Collection
     */
    protected $copySets = array();

    /**
     * var Collection
     */
    protected $availableAttributes = array();

    /**
     * Construct with full name of concrete impl of set and group class
     * @param string $setClass
     * @param string $groupClass
     * @param string $attributeClass
     * @param Collection $copySets
     * @param Collection $availableAttributes
     */
    public function __construct($setClass, $groupClass, $attributeClass, $copySets, $availableAttributes)
    {
        $this->setClass   = $setClass;
        $this->groupClass = $groupClass;
        $this->attributeClass = $attributeClass;
        $this->copySets   = $copySets;
        $this->availableAttributes = $availableAttributes;
    }

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

        $builder->add('title', 'text', array('required' => true));

        // create by copy
        $builder->add(
            'copyfromset', 'choice', array(
                'choices'       => $this->copySets,
                'required'      => false,
                'property_path' => false
            )
        );

        // set groups
        $builder->add(
            'groups', 'collection',
            array(
                'type'         => new ProductGroupType($this->groupClass, $this->attributeClass),
                'by_reference' => true,
                'allow_add'    => true,
                'allow_delete' => true
            )
        );

        // available attributes (not related to current set)
        $builder->add(
            'others', 'collection',
            array(
                'type'          => new ProductGroupAttributeType($this->attributeClass),
                'property_path' => false
            )
        );
        // add attributes
        foreach ($this->availableAttributes as $attribute) {
            $builder->get('others')->add(
                'attribute_'.$attribute->getId(),
                new ProductGroupAttributeType($this->attributeClass, $attribute)
            );
        }
    }

    /**
     * Setup default options
     * @param OptionsResolverInterface $resolver
    */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => $this->setClass
            )
        );
    }

    /**
     * Get identifier
     * @return string
     */
    public function getName()
    {
         return 'pim_catalogbundle_productattributeset';
    }
}