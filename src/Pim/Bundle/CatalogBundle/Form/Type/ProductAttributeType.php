<?php
namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pim\Bundle\CatalogBundle\Model\BaseFieldFactory;

/**
 * Type for attribute form (independant of persistence)
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright Copyright (c) 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductAttributeType extends AbstractType
{
    /**
     * @var string
     */
    protected $attributeClass;

    /**
     * Construct with full name of concrete impl of attribute class
     * @param unknown_type $attributeClass
     */
    public function __construct($attributeClass)
    {
        $this->attributeClass = $attributeClass;
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

        $builder->add('title', 'text', array('required' => true));

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

        if ($entity->getType() == BaseFieldFactory::FIELD_SELECT) {
            $builder->add(
                'options', 'collection', array(
                    'type' => new ProductAttributeOptionType(),
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => true,
                )
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
                'data_class' => $this->attributeClass
            )
        );
    }

    /**
     * Get identifier
     * @return string
     */
    public function getName()
    {
        return 'pim_catalogbundle_ProductAttributetype';
    }
}
