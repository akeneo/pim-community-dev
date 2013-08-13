<?php

namespace Pim\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

/**
 * Type for available product attributes
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AvailableProductAttributesType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'attributes',
            'entity',
            array(
                'class' => 'PimProductBundle:ProductAttribute',
                'query_builder' => function (EntityRepository $repository) use ($options) {
                    return $repository->getFindAllExceptQB($options['attributes']);
                },
                'multiple' => true,
                'expanded' => false,
                'group_by' => 'virtualGroup.name',
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
                'data_class' => 'Pim\Bundle\ProductBundle\Model\AvailableProductAttributes',
                'attributes' => array(),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_available_product_attributes';
    }
}
