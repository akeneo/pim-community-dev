<?php
namespace Pim\Bundle\ProductBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Pim\Bundle\TranslationBundle\Form\Type\TranslationType;

/**
 * Product segmentation translation form type
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ProductSegmentTranslationType extends TranslationType
{

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => 'Pim\Bundle\ProductBundle\Entity\ProductSegmentTranslation'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product_segment_translation';
    }
}
