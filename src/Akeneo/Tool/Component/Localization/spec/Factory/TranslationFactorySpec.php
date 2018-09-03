<?php

namespace spec\Akeneo\Tool\Component\Localization\Factory;

use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Tool\Component\Localization\Factory\TranslationFactory;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupTranslation;

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
            AttributeGroupTranslation::class,
            'Pim\Bundle\TranslationBundle\Tests\Entity\Item',
            'bar'
        );
        $this->createTranslation('en_US')
            ->shouldReturnAnInstanceOf(AttributeGroupTranslation::class);

        $this->createTranslation('en_US')
            ->getLocale()
            ->shouldReturn('en_US');
    }

    function it_throws_an_exception_when_an_invalid_translation_class_is_provided()
    {
        $this->beConstructedWith(
            LocaleInterface::class,
            'Pim\Bundle\TranslationBundle\Tests\Entity\Item',
            'bar'
        );
        $this->shouldThrow('\InvalidArgumentException');
    }
}
