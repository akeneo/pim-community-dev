<?php

namespace Pim\Bundle\UserBundle\Form\Type;

use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Pim\Bundle\UserBundle\Form\Transformer\AccessLevelToBooleanTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Overriden AclAccessLevelSelectorType
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AclAccessLevelSelectorType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addViewTransformer(
            new AccessLevelToBooleanTransformer(),
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'checkbox';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_acl_access_level_selector';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $choices = [
            AccessLevel::NONE_LEVEL,
            AccessLevel::SYSTEM_LEVEL
        ];

        $resolver->setDefaults(
            [
                'choices' => array_combine($choices, $choices)
            ]
        );
    }
}
