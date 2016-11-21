<?php

namespace spec\Pim\Bundle\VersioningBundle\Normalizer\Flat;

use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeTranslation;
use Akeneo\Component\Localization\Model\TranslatableInterface;

class TranslationNormalizerSpec extends ObjectBehavior
{
    function it_supports_flat_format()
    {
        $translations = ['labels' => []];
        $this->supportsNormalization($translations, 'flat')->shouldReturn(true);
        $this->supportsNormalization($translations, 'csv')->shouldReturn(false);
    }

    function it_normalizes_array_of_labels()
    {
        $translations = [
            'en_US' => 'My label',
            'fr_FR' => 'Mon label',
            'es_ES' => ''
        ];

        $this->normalize($translations, 'flat')->shouldReturn([
            'label-en_US' => 'My label',
            'label-fr_FR' => 'Mon label',
            'label-es_ES' => '',
        ]);
    }

    function it_normalizes_array_of_labels_given_locales_in_the_context()
    {
        $translations = [
            'en_US' => 'My label',
            'fr_FR' => 'Mon label',
            'es_ES' => ''
        ];

        $this->normalize($translations, 'flat')->shouldReturn(
            [
                'label-en_US' => 'My label',
                'label-fr_FR' => 'Mon label',
                'label-es_ES' => '',
            ]
        );
    }
}
