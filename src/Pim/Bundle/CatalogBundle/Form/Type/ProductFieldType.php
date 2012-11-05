<?php
namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pim\Bundle\CatalogBundle\Model\BaseFieldFactory;

/**
 * Type for field form (independant of persistence)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductFieldType extends AbstractType
{
    /**
     * @var string
     */
    protected $fieldClass;

    /**
     * Construct with full name of concrete impl of field class
     * @param unknown_type $fieldClass
     */
    public function __construct($fieldClass)
    {
        $this->fieldClass = $fieldClass;
    }

    /**
     * Build form
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $options['data'];

        // TODO drive from type and not add if in twig template ?
        $builder->add('id', 'hidden');

        $builder->add(
            'code', 'text', array(
                'disabled'  => ($entity->getId())? true : false
            )
        );

        // if already exists disabled this choice
        $builder->add(
            'type', 'choice', array(
                'choices'   => BaseFieldFactory::getTypeOptions(),
                'required'  => true,
                'disabled'  => ($entity->getId())? true : false
            )
        );

        $builder->add(
            'scope', 'choice', array(
                'choices'   => BaseFieldFactory::getScopeOptions(),
                'required'  => true,
                'label'     => 'Scope'
            )
        );

        $builder->add(
            'uniqueValue', 'choice', array(
                'choices'   => array(false => 'No', true => 'Yes'),
                'required'  => true,
                'label'     => 'Is unique'
            )
        );

        $builder->add(
            'valueRequired', 'choice', array(
                'choices'   => array(false => 'No', true => 'Yes'),
                'required'  => true,
                'label'     => 'Value required'
            )
        );

        $builder->add(
            'searchable', 'choice', array(
                'choices'   => array(false => 'No', true => 'Yes'),
                'required'  => true,
                'label'     => 'Is searchable'
            )
        );

        $builder->add(
            'options', 'collection', array(
                'type' => new ProductFieldOptionType(),
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            )
        );
    }

    /**
     * Setup default options
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => $this->fieldClass
            )
        );
    }

    /**
     * Get identifier
     * @return string
     */
    public function getName()
    {
        return 'pim_catalogbundle_productfieldtype';
    }
}
