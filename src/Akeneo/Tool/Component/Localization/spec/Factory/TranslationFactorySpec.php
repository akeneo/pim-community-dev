<?php

namespace spec\Akeneo\Tool\Component\Localization\Factory;

use Akeneo\Tool\Component\Localization\Factory\TranslationFactory;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroupTranslation;

class TranslationFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(
             AttributeGroupTranslation::class,
            'Pim\Bundle\TranslationBundle\Tests\Entity\Item',
            'bar'
        );
        $this->shouldHaveType(TranslationFactory::class);
    }

    function it_creates_a_translation()
    {
        $this->beConstructedWith(
            'Pim\Bundle\CatalogBundle\Entity\AttributeGroupTranslation',
            'Pim\Bundle\TranslationBundle\Tests\Entity\Item',
            'bar'
        );
        $this->createTranslation('en_US')
            ->shouldReturnAnInstanceOf('Pim\Bundle\CatalogBundle\Entity\AttributeGroupTranslation');

        $this->createTranslation('en_US')
            ->getLocale()
            ->shouldReturn('en_US');
    }

    function it_throws_an_exception_when_an_invalid_translation_class_is_provided()
    {
        $this->beConstructedWith(
            'Pim\Component\Catalog\Model\LocaleInterface',
            'Pim\Bundle\TranslationBundle\Tests\Entity\Item',
            'bar'
        );
        $this->shouldThrow('\InvalidArgumentException');
    }
}
