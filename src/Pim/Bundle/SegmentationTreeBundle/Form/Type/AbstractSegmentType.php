<?php
namespace Pim\Bundle\SegmentationTreeBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

use Symfony\Component\Form\AbstractType;

/**
 * Type for segment form
 *
 *
 * @abstract
 */
abstract class AbstractSegmentType extends AbstractType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $builder->add('code');
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_segmentation_tree';
    }
}
