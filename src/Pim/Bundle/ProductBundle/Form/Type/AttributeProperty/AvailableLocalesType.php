<?php

namespace Pim\Bundle\ProductBundle\Form\Type\AttributeProperty;

use Pim\Bundle\ProductBundle\Entity\Repository\LocaleRepository;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;

/**
 * Form type related to availableLocales property of ProductAttribute
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AvailableLocalesType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'entity';
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'required' => false,
                'multiple' => true,
                'class' => 'Pim\Bundle\ProductBundle\Entity\Locale',
                'query_builder' => function (LocaleRepository $repository) {
                    return $repository->getActivatedLocalesQB();
                }
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_product_available_locales';
    }
}
