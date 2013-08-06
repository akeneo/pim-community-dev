<?php

namespace Pim\Bundle\ConfigBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Doctrine\ORM\EntityRepository;

/**
 * Type for channel form
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ChannelType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('id', 'hidden');

        $builder->add('code');

        $builder->add(
            'name',
            'text',
            array(
                'label' => 'Default label'
            )
        );

        $builder->add(
            'currencies',
            'entity',
            array(
                'required'      => true,
                'multiple'      => true,
                'class'         => 'Pim\Bundle\ConfigBundle\Entity\Currency',
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->getActivatedCurrenciesQB();
                }
            )
        );

        $builder->add('locales', 'pim_product_available_locales');

        $builder->add(
            'category',
            'entity',
            array(
                'label'         => 'Category tree',
                'required'      => true,
                'class'         => 'Pim\Bundle\ProductBundle\Entity\Category',
                'query_builder' => function (EntityRepository $repository) {
                    return $repository->getTreesQB();
                }
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\Bundle\ConfigBundle\Entity\Channel'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_config_channel';
    }
}
