<?php

namespace Pim\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Oro\Bundle\SecurityBundle\Form\Type\AclPrivilegeType;
use Oro\Bundle\UserBundle\Form\Type\AclRoleType as OroAclRoleType;

/**
 * Overriden AclRoleType to remove ACLs for disabled locales
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AclRoleType extends OroAclRoleType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->privilegeConfig as $fieldName => $config) {
            $builder->add(
                $fieldName,
                'oro_acl_collection',
                array(
                    'type' => new AclPrivilegeType(),
                    'allow_add' => true,
                    'prototype' => false,
                    'allow_delete' => false,
                    'mapped' => false,
                    'options' => array(
                        'privileges_config' => $config,
                    )
                )
            );
        }

        // Empty the privilege config to prevent parent from overriding the fields
        $this->privilegeConfig = array();

        parent::buildForm($builder, $options);
    }
}
