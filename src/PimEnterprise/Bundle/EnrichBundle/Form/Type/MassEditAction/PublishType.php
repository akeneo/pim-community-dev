<?php

namespace PimEnterprise\Bundle\EnrichBundle\Form\Type\MassEditAction;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Form type of the Publish operation
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class PublishType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('notGrantedIdentifiers', 'hidden');
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {

        $resolver->setDefaults(
            [
                'data_class' => 'PimEnterprise\\Bundle\\EnrichBundle\\MassEditAction\\Operation\\Publish',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pimee_enrich_mass_publish';
    }
}
