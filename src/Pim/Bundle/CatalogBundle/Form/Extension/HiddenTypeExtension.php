<?php

namespace Pim\Bundle\CatalogBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Oro\Bundle\FormBundle\Form\DataTransformer\ArrayToStringTransformer;

/**
 * Hidden form type extension which allowed to store multivalued property
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class HiddenTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (array_key_exists('multiple', $options) && true === $options['multiple']) {
            $builder->addViewTransformer(new ArrayToStringTransformer(',', true));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setOptional(array('multiple'));
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return 'hidden';
    }
}
