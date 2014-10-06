<?php

namespace Pim\Bundle\EnrichBundle\Form\Type;

use Pim\Bundle\EnrichBundle\Form\Subscriber\DisableFieldSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Type for group type form
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupTypeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('code');
        $builder->add(
            'label',
            'pim_translatable_field',
            array(
                'field'             => 'label',
                'translation_class' => 'Pim\\Bundle\\CatalogBundle\\Entity\\GroupTypeTranslation',
                'entity_class'      => 'Pim\\Bundle\\CatalogBundle\\Entity\\GroupType',
                'property_path'     => 'translations'
            )
        );
        $builder->addEventSubscriber(new DisableFieldSubscriber('code'));
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\Bundle\CatalogBundle\Entity\GroupType'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_enrich_group_type';
    }
}
