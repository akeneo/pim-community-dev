<?php

namespace Pim\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pim\Bundle\ProductBundle\Form\Subscriber\AddAttributeAsLabelSubscriber;
use Pim\Bundle\ProductBundle\Form\Type\AttributeRequirementType;
use Pim\Bundle\ProductBundle\Form\Subscriber\AddAttributeRequirementsSubscriber;

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
        $factory = $builder->getFormFactory();
        $channels   = $options['channels'];
        $attributes = $options['attributes'];

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
            ->add('attributeRequirements', 'collection', array('type' => new AttributeRequirementType))
            ->addEventSubscriber(new AddAttributeAsLabelSubscriber($factory))
            ->addEventSubscriber(new AddAttributeRequirementsSubscriber($options['channels'], $options['attributes']));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\Bundle\ProductBundle\Entity\Family',
                'channels'   => array(),
                'attributes' => array(),
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
