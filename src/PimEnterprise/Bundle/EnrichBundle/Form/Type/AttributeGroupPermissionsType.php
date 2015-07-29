<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for AttributeGroup permissions
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class AttributeGroupPermissionsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'view',
            'pimee_security_groups',
            ['label' => 'attribute group.permissions.view.label', 'help' => 'attribute group.permissions.view.help']
        );
        $builder->add(
            'edit',
            'pimee_security_groups',
            ['label' => 'attribute group.permissions.edit.label', 'help' => 'attribute group.permissions.edit.help']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['mapped' => false]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pimee_enrich_attribute_group_permissions';
    }
}
