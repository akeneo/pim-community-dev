<?php
namespace Pim\Bundle\ProductBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

use Oro\Bundle\FlexibleEntityBundle\Form\Type\MediaType as FlexibleMediaType;

/**
 * Extended MediaType
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class MediaType extends FlexibleMediaType
{

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');

        $builder->add('file', 'file', array('required' => false));

        $builder->add('remove', 'checkbox', array(
            'required' => false,
            'property_path' => false,
            'label' => 'Remove media'
        ));
    }
}
