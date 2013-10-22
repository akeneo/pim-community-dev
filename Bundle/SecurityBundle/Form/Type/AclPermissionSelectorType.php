<?php

namespace Oro\Bundle\SecurityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Oro\Bundle\SecurityBundle\Acl\AccessLevel;

class AclPermissionSelectorType extends AbstractType
{
    const NONE_LEVEL = 'None';
    const BASIC_LEVEL = 'User (BASIC_LEVEL)';
    const LOCAL_LEVEL = 'Business Unit (LOCAL_LEVEL)';
    const DEEP_LEVEL = 'Business Unit share (DEEP_LEVEL)';
    const GLOBAL_LEVEL = 'Organization (GLOBAL_LEVEL)';
    const SYSTEM_LEVEL = 'System (DEEP_LEVEL)';

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'choice';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_acl_permission_selector';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => array(
                AccessLevel::NONE_LEVEL => self::NONE_LEVEL,
                AccessLevel::BASIC_LEVEL => self::BASIC_LEVEL,
                AccessLevel::LOCAL_LEVEL => self::LOCAL_LEVEL,
                AccessLevel::DEEP_LEVEL => self::DEEP_LEVEL,
                AccessLevel::GLOBAL_LEVEL => self::GLOBAL_LEVEL,
                AccessLevel::SYSTEM_LEVEL => self::SYSTEM_LEVEL,
            )
        ));
    }
}