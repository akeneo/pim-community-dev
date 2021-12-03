<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Channel\GetChannelTranslations;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\AttributeColumnTranslator;
use Akeneo\Tool\Component\Localization\LanguageTranslator;
use PhpSpec\ObjectBehavior;

class AttributeColumnTranslatorSpec extends ObjectBehavior
{
    function let(
        GetAttributes $getAttributes,
        LanguageTranslator $languageTranslator,
        GetChannelTranslations $getChannelTranslations
    ) {
        $this->beConstructedWith($getAttributes, $languageTranslator, $getChannelTranslations);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeColumnTranslator::class);
    }

    function it_translates_an_attribute_column(GetAttributes $getAttributes)
    {
        $getAttributes->forCode('nutrition')->willReturn(new Attribute(
            'nutrition',
            AttributeTypes::TABLE,
            [],
            false,
            false,
            null,
            null,
            null,
            '',
            ['en_US' => 'Nutrition']
        ));
        $this->translate('nutrition', 'en_US')->shouldReturn('Nutrition');
    }

    function it_translates_a_localizable_attribute_column(
        GetAttributes $getAttributes,
        LanguageTranslator $languageTranslator
    ) {
        $getAttributes->forCode('localizable_nutrition')->willReturn(new Attribute(
            'nutrition',
            AttributeTypes::TABLE,
            [],
            true,
            false,
            null,
            null,
            null,
            '',
            ['fr_FR' => 'foo', 'en_US' => 'Nutrition']
        ));
        $languageTranslator->translate('fr_FR', 'en_US', \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, 'fr_FR'))
            ->willReturn('Français');

        $this->translate('localizable_nutrition-fr_FR', 'en_US')->shouldReturn('Nutrition (Français)');
    }

    function it_translates_a_scopable_attribute_column(
        GetAttributes $getAttributes,
        GetChannelTranslations $getChannelTranslations
    ) {
        $getAttributes->forCode('scopable_nutrition')->willReturn(new Attribute(
            'nutrition',
            AttributeTypes::TABLE,
            [],
            false,
            true,
            null,
            null,
            null,
            '',
            ['fr_FR' => 'foo', 'en_US' => 'Nutrition']
        ));
        $getChannelTranslations->byLocale('en_US')->willReturn(['eco' => 'Ecommerce']);

        $this->translate('scopable_nutrition-eco', 'en_US')->shouldReturn('Nutrition (Ecommerce)');
    }

    function it_translates_a_localizable_scopable_attribute_column(
        GetAttributes $getAttributes,
        LanguageTranslator $languageTranslator,
        GetChannelTranslations $getChannelTranslations
    ) {
        $getAttributes->forCode('localizable_scopable_nutrition')->willReturn(new Attribute(
            'nutrition',
            AttributeTypes::TABLE,
            [],
            true,
            true,
            null,
            null,
            null,
            '',
            ['fr_FR' => 'foo', 'en_US' => 'Nutrition']
        ));
        $languageTranslator->translate('fr_FR', 'en_US', \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, 'fr_FR'))
            ->willReturn('Français');
        $getChannelTranslations->byLocale('en_US')->willReturn(['eco' => 'Ecommerce']);

        $this->translate('localizable_scopable_nutrition-fr_FR-eco', 'en_US')->shouldReturn('Nutrition (Français, Ecommerce)');
    }

    function it_translates_a_localizable_scopable_attribute_column_with_fallbacks(
        GetAttributes $getAttributes,
        LanguageTranslator $languageTranslator,
        GetChannelTranslations $getChannelTranslations
    ) {
        $getAttributes->forCode('localizable_scopable_nutrition')->willReturn(new Attribute(
            'nutrition',
            AttributeTypes::TABLE,
            [],
            true,
            true,
            null,
            null,
            null,
            '',
            ['fr_FR' => 'foo']
        ));
        $languageTranslator->translate('fr_FR', 'en_US', \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, 'fr_FR'))
            ->willReturn('[fr_FR]');
        $getChannelTranslations->byLocale('en_US')->willReturn([]);

        $this->translate('localizable_scopable_nutrition-fr_FR-eco', 'en_US')
            ->shouldReturn('[localizable_scopable_nutrition] ([fr_FR], [eco])');
    }
}
