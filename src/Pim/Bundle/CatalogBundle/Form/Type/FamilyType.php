<?php

namespace Pim\Bundle\CatalogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Pim\Bundle\CatalogBundle\Form\Type\AttributeRequirementType;
use Pim\Bundle\CatalogBundle\Form\Subscriber\AddAttributeAsLabelSubscriber;
use Pim\Bundle\CatalogBundle\Form\Subscriber\AddAttributeRequirementsSubscriber;
use Pim\Bundle\CatalogBundle\Form\Subscriber\DisableFieldSubscriber;

/**
 * Type for family form
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
                    'translation_class' => 'Pim\\Bundle\\CatalogBundle\\Entity\\FamilyTranslation',
                    'entity_class'      => 'Pim\\Bundle\\CatalogBundle\\Entity\\Family',
                    'property_path'     => 'translations'
                )
            )
            ->add('attributeRequirements', 'collection', array('type' => new AttributeRequirementType()))
            ->addEventSubscriber(new AddAttributeAsLabelSubscriber($factory))
            ->addEventSubscriber(new AddAttributeRequirementsSubscriber($channels, $attributes))
            ->addEventSubscriber(new DisableFieldSubscriber('code'));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\Bundle\CatalogBundle\Entity\Family',
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
