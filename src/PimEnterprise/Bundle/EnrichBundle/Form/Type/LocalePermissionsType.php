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
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Form type for Locale permissions
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class LocalePermissionsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'view',
            'pimee_security_groups',
            ['label' => 'locale.permissions.view.label', 'help' => 'locale.permissions.view.help']
        );
        $builder->add(
            'edit',
            'pimee_security_groups',
            ['label' => 'locale.permissions.edit.label', 'help' => 'locale.permissions.edit.help']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(['mapped' => false]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pimee_enrich_locale_permissions';
    }
}
