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
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
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
     * @var string
     */
    protected $optionClass;

    /**
     * Construct with full name of concrete impl of attribute and option classes
     * 
     * @param string $attributeClass
     * @param string $optionClass
     */
    public function __construct($attributeClass, $optionClass)
    {
        $this->attributeClass = $attributeClass;
        $this->optionClass = $optionClass;
    }

    /**
     * Build form
     * @param FormBuilderInterface $builder
     * @param array                $options
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

        $builder->add(
            'translatable', 'choice', array(
                'choices'   => array(false => 'No', true => 'Yes'),
                'required'  => true,
                'label'     => 'Is translatable'
            )
        );

        if ($entity->getType() == BaseFieldFactory::FIELD_SELECT) {
            $builder->add(
                'options', 'collection', array(
                    'type'         => new ProductAttributeOptionType($this->optionClass),
                    'allow_add'    => true,
                    'allow_delete' => true,
                    'by_reference' => false,
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
        return 'pim_catalogbundle_productattributetype';
    }
}
