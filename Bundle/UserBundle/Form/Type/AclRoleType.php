<?php

namespace Oro\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Symfony\Component\Form\AbstractType;

use Oro\Bundle\SecurityBundle\Form\Type\PrivilegeCollectionType;
use Oro\Bundle\SecurityBundle\Form\Type\AclPrivilegeType;

class AclRoleType extends AbstractType
{
    /**
     * @var array privilege fields config
     */
    protected $privilegeConfig;

    /**
     * @param array $privilegeTypeConfig
     */
    public function __construct(array $privilegeTypeConfig)
    {
        $this->privilegeConfig = $privilegeTypeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'role',
            'text',
            array(
                'required' => true,
            )
        );
        $builder->add(
            'label',
            'text',
            array(
                'required' => false,
            )
        );

        foreach ($this->privilegeConfig as $fieldName => $config) {
            $builder->add(
                $fieldName,
                new PrivilegeCollectionType(),
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

        $builder->add(
            'appendUsers',
            'oro_entity_identifier',
            array(
                'class'    => 'OroUserBundle:User',
                'required' => false,
                'mapped'   => false,
                'multiple' => true,
            )
        );

        $builder->add(
            'removeUsers',
            'oro_entity_identifier',
            array(
                'class'    => 'OroUserBundle:User',
                'required' => false,
                'mapped'   => false,
                'multiple' => true,
            )
        );

        $factory = $builder->getFormFactory();

        // disable role name edit after role has been created
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($factory) {
                if ($event->getData() && $event->getData()->getId()) {
                    $form = $event->getForm();

                    $options = $form->get('role')->getConfig()->getOptions();
                    if (array_key_exists('auto_initialize', $options)) {
                        $options['auto_initialize'] = false;
                    }

                    $form->add(
                        $factory->createNamed(
                            'role',
                            'text',
                            null,
                            array_merge(
                                $options,
                                array('disabled' => true)
                            )
                        )
                    );
                }
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Oro\Bundle\UserBundle\Entity\Role',
                'intention'  => 'role',
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_user_role';
    }
}
