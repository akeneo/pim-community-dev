<?php

namespace Pim\Bundle\UIBundle\Form\Type;

use Pim\Bundle\UIBundle\Form\Transformer\BooleanToStringTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for a on/off switch field
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SwitchType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setData(isset($options['data']) ? $options['data'] : false);
        $builder->resetViewTransformers();
        $builder->addViewTransformer(new BooleanToStringTransformer($options['value']));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'attr' => array(
                    'size'           => 'small',
                    'data-on-label'  => 'switch_on',
                    'data-off-label' => 'switch_off',
                )
            )
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
        return 'switch';
    }
}
