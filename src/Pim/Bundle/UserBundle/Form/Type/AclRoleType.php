<?php

namespace Pim\Bundle\UserBundle\Form\Type;

use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclPrivilegeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Overriden AclRoleType to remove ACLs for disabled locales
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AclRoleType extends AbstractType
{
    /** @var array privilege fields config */
    protected $privilegeConfig = [];

    /** @var AclManager $aclManager */
    protected $aclManager;

    /**
     * @param array $privilegeTypeConfig
     */
    public function __construct(AclManager $aclManager, array $privilegeConfig)
    {
        $this->aclManager      = $aclManager;
        $this->privilegeConfig = $privilegeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('label', 'text', [
                'required' => true,
                'label'    => 'Role'
            ])
            ->add('appendUsers', 'oro_entity_identifier', [
                'class'    => 'PimUserBundle:User',
                'required' => false,
                'mapped'   => false,
                'multiple' => true,
            ])
            ->add('removeUsers', 'oro_entity_identifier', [
                'class'    => 'PimUserBundle:User',
                'required' => false,
                'mapped'   => false,
                'multiple' => true,
            ]);

        $aclManager = $this->aclManager;
        $privilegeConfig = $this->privilegeConfig;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($aclManager, $privilegeConfig) {
            $role = $event->getData();

            if (null === $role || !is_object($role)) {
                return;
            }

            $form = $event->getForm();
            $repository = $aclManager->getPrivilegeRepository();

            foreach ($privilegeConfig as $name => $config) {
                $privileges  = $repository->getPrivileges($aclManager->getSid($role))->filter(
                    function ($entry) use ($config) {
                        return in_array($entry->getExtensionKey(), $config['types']);
                    }
                );

                if (!$config['show_default']) {
                    foreach ($privileges as $privilege) {
                        if ($privilege->getIdentity()->getName() == AclPrivilegeRepository::ROOT_PRIVILEGE_NAME) {
                            $privileges->removeElement($privilege);
                        }
                    }
                }

                $form->add($name, 'oro_acl_collection', [
                    'type'         => 'oro_acl_privilege',
                    'data'         => $privileges,
                    'allow_add'    => true,
                    'prototype'    => false,
                    'allow_delete' => false,
                    'mapped'       => false,
                    'options'      => [
                        'privileges_config' => array_merge($config, [
                            'permissions' => $repository->getPermissionNames($config['types'])
                        ]),
                    ]
                ]);
            }
        });

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $role = $event->getData();

            $role->setRole(strtoupper(trim(preg_replace('/[^\w\-]/i', '_', $role->getLabel()))));
        });

        $builder->get('appendUsers')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $users = $event->getData();
            $group = $event->getForm()->getParent()->getData();

            foreach ($users as $user) {
                $user->addRole($group);
            }
        });

        $builder->get('removeUsers')->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $users = $event->getData();
            $group = $event->getForm()->getParent()->getData();

            foreach ($users as $user) {
                $user->removeRole($group);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Pim\Bundle\UserBundle\Entity\Role',
            'intention'  => 'role',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_user_role_form';
    }
}
