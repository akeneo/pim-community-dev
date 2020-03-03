<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Validator;

use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Validator\AttributeFilterValidatorHelper;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use PhpSpec\ObjectBehavior;

class AttributeFilterValidatorHelperSpec extends ObjectBehavior
{
    function let(LocaleRepositoryInterface $localeRepository, ChannelRepositoryInterface $scopeRepository)
    {
        $this->beAnInstanceOf(InitializedAttributeFilterValidatorHelper::class);
        $this->beConstructedWith($localeRepository, $scopeRepository);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeFilterValidatorHelper::class);
    }

    function it_does_not_throw_an_exception_when_localizable_requirement_is_respected(
        AttributeInterface $description,
        AttributeInterface $name
    ) {
        $description->isLocalizable()->willReturn(true);
        $description->isLocaleSpecific()->willReturn(false);
        $description->getCode()->willReturn('description');
        $name->isLocalizable()->willReturn(false);
        $name->getCode()->willReturn('name');

        $this->validateLocale($description, 'en_US');
        $this->validateLocale($name, null);
    }

    function it_throws_an_exception_when_attribute_localizable_requirement_is_not_respected(
        AttributeInterface $description,
        AttributeInterface $name
    ) {
        $description->isLocalizable()->willReturn(true);
        $description->isLocaleSpecific()->willReturn(false);
        $description->getCode()->willReturn('description');
        $name->isLocalizable()->willReturn(false);
        $name->getCode()->willReturn('name');

        $this->shouldThrow(new \LogicException('Attribute "description" expects a locale, none given.'))
            ->during('validateLocale', [$description, null]);

        $this->shouldThrow(new \LogicException('Attribute "name" does not expect a locale, "en_US" given.'))
            ->during('validateLocale', [$name, 'en_US']);
    }

    function it_throws_an_exception_when_locale_is_not_activated(
        $localeRepository,
        AttributeInterface $description
    ) {
        $localeRepository->getActivatedLocaleCodes()->willReturn([]);
        $description->isLocalizable()->willReturn(true);
        $description->isLocaleSpecific()->willReturn(false);
        $description->getCode()->willReturn('description');

        $this->shouldThrow(
            new \LogicException('Attribute "description" expects an existing and activated locale, "de_DE" given.')
        )->during('validateLocale', [$description, 'de_DE']);
    }

    function it_does_not_throw_an_exception_when_scopable_requirement_is_respected(
        AttributeInterface $description,
        AttributeInterface $name
    ) {
        $description->isLocalizable()->willReturn(true);
        $description->isLocaleSpecific()->willReturn(false);
        $description->isScopable()->willReturn(true);
        $description->getCode()->willReturn('description');
        $name->isLocalizable()->willReturn(false);
        $name->isScopable()->willReturn(false);
        $name->getCode()->willReturn('name');

        $this->validateScope($description, 'ecommerce');
        $this->validateScope($name, null);
    }

    function it_throws_an_exception_when_attribute_scopable_requirement_is_not_respected(
        AttributeInterface $description,
        AttributeInterface $name
    ) {
        $description->isScopable()->willReturn(true);
        $description->getCode()->willReturn('description');
        $name->isScopable()->willReturn(false);
        $name->getCode()->willReturn('name');

        $this->shouldThrow(new \LogicException('Attribute "description" expects a scope, none given.'))
            ->during('validateScope', [$description, null]);

        $this->shouldThrow(new \LogicException('Attribute "name" does not expect a scope, "ecommerce" given.'))
            ->during('validateScope', [$name, 'ecommerce']);
    }

    function it_throws_an_exception_when_scope_is_not_activated(
        $scopeRepository,
        AttributeInterface $description
    ) {
        $scopeRepository->getChannelCodes()->willReturn([]);
        $description->isScopable()->willReturn(true);
        $description->getCode()->willReturn('description');

        $this->shouldThrow(
            new \LogicException('Attribute "description" expects an existing scope, "print" given.')
        )->during('validateScope', [$description, 'print']);
    }
}

class InitializedAttributeFilterValidatorHelper extends AttributeFilterValidatorHelper
{
    /** @var array */
    protected $localeCodes = ['en_US', 'fr_FR'];

    /** @var array */
    protected $scopeCodes = ['ecommerce', 'tablet'];
}
