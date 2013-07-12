<?php
namespace Pim\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Type for product family form
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class FamilyType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $builder->getData() ?: null;

        // TODO : attributes as label doesn't work anymore !!!!

        $builder
            ->add('code')
            ->add(
                'label',
                'pim_translatable_field',
                array(
                    'field'             => 'label',
                    'translation_class' => 'Pim\\Bundle\\ProductBundle\\Entity\\FamilyTranslation',
                    'entity_class'      => 'Pim\\Bundle\\ProductBundle\\Entity\\Family',
                    'property_path'     => 'translations'
                )
            )
            ->add(
                'attributeAsLabel',
                'entity',
                array(
                    'required'    => false,
                    'empty_value' => 'Id',
                    'label'       => 'Attribute used as label',
                    'choices'     => $data ? $data->getAttributeAsLabelChoices() : array(),
                    'class'       => 'Pim\Bundle\ProductBundle\Entity\ProductAttribute',
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\Bundle\ProductBundle\Entity\Family'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_family';
    }
}
