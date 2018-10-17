<?php

namespace Akeneo\Platform\Bundle\UIBundle\Form\Type;

use Akeneo\Pim\Enrichment\Bundle\Form\Subscriber\FilterLocaleSpecificValueSubscriber;
use Akeneo\Pim\Enrichment\Bundle\Form\Subscriber\FilterLocaleValueSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Localized collection type
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizedCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(
            new FilterLocaleValueSubscriber(
                $options['currentLocale'],
                $options['comparisonLocale']
            )
        );
        $builder->addEventSubscriber(
            new FilterLocaleSpecificValueSubscriber(
                $options['currentLocale']
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'currentLocale'    => null,
                'comparisonLocale' => null,
            ]
        );
    }

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
    public function getBlockPrefix()
    {
        return 'pim_enrich_localized_collection';
    }
}
