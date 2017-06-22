<?php

namespace Pim\Bundle\EnrichBundle\Form\Type\AttributeProperty;

use Pim\Bundle\EnrichBundle\Form\Type\AttributeOptionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type related to options property of AttributeInterface
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OptionsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'type'         => AttributeOptionType::class,
                'allow_add'    => true,
                'allow_delete' => true,
                'by_reference' => false
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'pim_enrich_options';
    }
}
