<?php

namespace Pim\Bundle\EnrichBundle\Form\Type\AttributeProperty;

use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type related to availableLocales property of AttributeInterface
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
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'required'      => false,
                'multiple'      => true,
                'select2'       => true,
                'class'         => 'Pim\Bundle\CatalogBundle\Entity\Locale',
                'query_builder' => function (LocaleRepositoryInterface $repository) {
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
        return 'pim_enrich_available_locales';
    }
}
