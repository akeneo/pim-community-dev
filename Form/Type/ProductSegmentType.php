<?php
namespace Pim\Bundle\ProductBundle\Form\Type;

use Pim\Bundle\ProductBundle\Form\Subscriber\ProductSegmentSubscriber;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Type for product segment form
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductSegmentType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('code');

        $builder->add(
            'titles',
            'pim_translation_collection',
            array(
                'label' => 'Title',
                'type' => 'pim_translation'
            )
        );

        // Add isDynamic field is needed
        $subscriber = new ProductSegmentSubscriber($builder->getFormFactory());
        $builder->addEventSubscriber($subscriber);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\Bundle\ProductBundle\Entity\ProductSegment'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product_segment';
    }
}
