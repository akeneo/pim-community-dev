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

use Doctrine\ORM\EntityRepository;
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
                'label'    => 'user.email.notifications',
                'required' => false,
            ]
        );

        $builder->add(
            'assetDelayReminder',
            'integer',
            [
                'label'    => 'user.asset_delay_reminder',
                'required' => true,
            ]
        );

        $builder->add(
            'defaultAssetTree',
            'entity',
            [
                'class'         => 'PimEnterprise\\Component\\ProductAsset\\Model\\Category',
                'property'      => 'label',
                'select2'       => true,
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->getTreesQB();
                }
            ]
        );
    }
}
