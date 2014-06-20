<?php

namespace PimEnterprise\Bundle\UserBundle\Form\Handler;

use Symfony\Component\Form\FormFactory;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Form\Handler\AclRoleHandler as OroAclRoleHandler;
use PimEnterprise\Bundle\UserBundle\Form\Subscriber\CategoryOwnershipSubscriber;
use PimEnterprise\Bundle\UserBundle\Form\Type\AclRoleType;

/**
 * Overriden AclRoleHandler to add product ownership rights per category
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AclRoleHandler extends OroAclRoleHandler
{
    /**
     * @var CategoryOwnershipSubscriber
     */
    protected $subscriber;

    /**
     * @param FormFactory                 $formFactory
     * @param array                       $privilegeConfig
     * @param CategoryOwnershipSubscriber $subscriber
     */
    public function __construct(
        FormFactory $formFactory,
        array $privilegeConfig,
        CategoryOwnershipSubscriber $subscriber
    ) {
        parent::__construct($formFactory, $privilegeConfig);

        $this->subscriber = $subscriber;
    }

    /**
     * {@inheritdoc}
     */
    public function createForm(Role $role)
    {
        foreach ($this->privilegeConfig as $configName => $config) {
            $this->privilegeConfig[$configName]['permissions'] = $this->aclManager
                ->getPrivilegeRepository()->getPermissionNames($config['types']);
        }

        $this->form = $this->formFactory->create(
            new ACLRoleType(
                $this->privilegeConfig,
                $this->subscriber
            ),
            $role
        );

        return $this->form;
    }
}
