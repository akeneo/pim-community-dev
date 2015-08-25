<?php
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\UserBundle\Form\Type;

use Pim\Bundle\UserBundle\Form\Type\UserType as BaseUserType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Overridden user form to add field
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class UserType extends BaseUserType
{
    /**
     * {@inheritdoc}
     */
    protected function setDefaultUserFields(FormBuilderInterface $builder)
    {
        parent::setDefaultUserFields($builder);

        $builder->add(
            'emailNotifications',
            'checkbox',
            [
                'label'    => 'Email notifications',
                'required' => false,
            ]
        );

        $builder->add(
            'assetDelayReminder',
            'integer',
            [
                'label'    => 'Asset delay reminder (in days)',
                'required' => true,
            ]
        );
    }
}
