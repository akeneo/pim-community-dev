<?php

namespace Akeneo\UserManagement\Bundle\Form\Type;

use Akeneo\UserManagement\Bundle\Form\Transformer\AccessLevelToBooleanTransformer;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * AclAccessLevelSelector form type
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
        return CheckboxType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pim_acl_access_level_selector';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
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
