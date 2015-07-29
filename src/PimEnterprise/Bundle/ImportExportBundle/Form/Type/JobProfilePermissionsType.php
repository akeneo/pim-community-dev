<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ImportExportBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for job profile permissions
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class JobProfilePermissionsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('execute', 'pimee_security_groups', ['label' => 'job_profile.permissions.execute.label']);
        $builder->add('edit', 'pimee_security_groups', ['label' => 'job_profile.permissions.edit.label']);
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
        return 'pimee_import_export_job_profile_permissions';
    }
}
