<?php

namespace spec\Pim\Bundle\TranslationBundle\Factory;

use PhpSpec\ObjectBehavior;

class TranslationFactorySpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->beConstructedWith(
            'Pim\Bundle\CatalogBundle\Entity\AttributeGroupTranslation',
            'Pim\Bundle\TranslationBundle\Tests\Entity\Item',
            'bar'
        );
        $this->shouldHaveType('Pim\Bundle\TranslationBundle\Factory\TranslationFactory');
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
            'Pim\Bundle\CatalogBundle\Model\LocaleInterface',
            'Pim\Bundle\TranslationBundle\Tests\Entity\Item',
            'bar'
        );
        $this->shouldThrow('\InvalidArgumentException');
    }
}
