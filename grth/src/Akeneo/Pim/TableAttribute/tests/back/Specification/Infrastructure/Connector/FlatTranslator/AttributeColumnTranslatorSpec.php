<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Channel\GetChannelTranslations;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\AttributeColumnTranslator;
use Akeneo\Tool\Component\Localization\LanguageTranslator;
use PhpSpec\ObjectBehavior;

class AttributeColumnTranslatorSpec extends ObjectBehavior
{
    function let(
        AttributeRepositoryInterface $attributeRepository,
        LanguageTranslator $languageTranslator,
        GetChannelTranslations $getChannelTranslations
    ) {
        $this->beConstructedWith($attributeRepository, $languageTranslator, $getChannelTranslations);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(AttributeColumnTranslator::class);
    }

    function it_translates_an_attribute_column(AttributeRepositoryInterface $attributeRepository)
    {
        $attribute = new Attribute();
        $attribute->setLocalizable(false);
        $attribute->setScopable(false);
        $attribute->getTranslation('en_US')->setLabel('Nutrition');
        $attributeRepository->findOneByIdentifier('nutrition')->willReturn($attribute);

        $this->translate('nutrition', 'en_US')->shouldReturn('Nutrition');
    }

    function it_translates_a_localizable_attribute_column(
        AttributeRepositoryInterface $attributeRepository,
        LanguageTranslator $languageTranslator
    ) {
        $attribute = new Attribute();
        $attribute->setLocalizable(true);
        $attribute->setScopable(false);
        $attribute->getTranslation('fr_FR')->setLabel('foo');
        $attribute->getTranslation('en_US')->setLabel('Nutrition');
        $attributeRepository->findOneByIdentifier('localizable_nutrition')->willReturn($attribute);

        $languageTranslator->translate('fr_FR', 'en_US', \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, 'fr_FR'))
            ->willReturn('Français');

        $this->translate('localizable_nutrition-fr_FR', 'en_US')->shouldReturn('Nutrition (Français)');
    }

    function it_translates_a_scopable_attribute_column(
        AttributeRepositoryInterface $attributeRepository,
        GetChannelTranslations $getChannelTranslations
    ) {
        $attribute = new Attribute();
        $attribute->setLocalizable(false);
        $attribute->setScopable(true);
        $attribute->getTranslation('fr_FR')->setLabel('foo');
        $attribute->getTranslation('en_US')->setLabel('Nutrition');
        $attributeRepository->findOneByIdentifier('scopable_nutrition')->willReturn($attribute);
        $getChannelTranslations->byLocale('en_US')->willReturn(['eco' => 'Ecommerce']);

        $this->translate('scopable_nutrition-eco', 'en_US')->shouldReturn('Nutrition (Ecommerce)');
    }

    function it_translates_a_localizable_scopable_attribute_column(
        AttributeRepositoryInterface $attributeRepository,
        LanguageTranslator $languageTranslator,
        GetChannelTranslations $getChannelTranslations
    ) {
        $attribute = new Attribute();
        $attribute->setLocalizable(true);
        $attribute->setScopable(true);
        $attribute->getTranslation('fr_FR')->setLabel('foo');
        $attribute->getTranslation('en_US')->setLabel('Nutrition');
        $attributeRepository->findOneByIdentifier('localizable_scopable_nutrition')->willReturn($attribute);

        $languageTranslator->translate('fr_FR', 'en_US', \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, 'fr_FR'))
            ->willReturn('Français');
        $getChannelTranslations->byLocale('en_US')->willReturn(['eco' => 'Ecommerce']);

        $this->translate('localizable_scopable_nutrition-fr_FR-eco', 'en_US')->shouldReturn('Nutrition (Français, Ecommerce)');
    }

    function it_translates_a_localizable_scopable_attribute_column_with_fallbacks(
        AttributeRepositoryInterface $attributeRepository,
        LanguageTranslator $languageTranslator,
        GetChannelTranslations $getChannelTranslations
    ) {
        $attribute = new Attribute();
        $attribute->setLocalizable(true);
        $attribute->setScopable(true);
        $attribute->getTranslation('fr_FR')->setLabel('foo');
        $attributeRepository->findOneByIdentifier('localizable_scopable_nutrition')->willReturn($attribute);
        $languageTranslator->translate('fr_FR', 'en_US', \sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, 'fr_FR'))
            ->willReturn('[fr_FR]');
        $getChannelTranslations->byLocale('en_US')->willReturn([]);

        $this->translate('localizable_scopable_nutrition-fr_FR-eco', 'en_US')
            ->shouldReturn('[localizable_scopable_nutrition] ([fr_FR], [eco])');
    }
}
