<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\CriteriaEvaluation\Consistency;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleDataCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\LocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValues;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\GetAllActivatedLocalesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

class FilterProductValuesForTitleFormattingSpec extends ObjectBehavior
{
    public function let(GetAllActivatedLocalesQueryInterface $allActivatedLocalesQuery)
    {
        $this->beConstructedWith($allActivatedLocalesQuery);
    }

    public function it_returns_the_main_title_text_values_when_one_locale_is_activated($allActivatedLocalesQuery)
    {
        $allActivatedLocalesQuery->execute()->willReturn(new LocaleCollection([new LocaleCode('en_US')]));

        $localizableTextValues1 = $this->givenLocalizableTextValues('localizable_text_1');
        $localizableTextValues2 = $this->givenLocalizableTextValues('localizable_text_2');
        $notLocalizableTextValues = $this->givenNotLocalizableTextValues('not_localizable_text');
        $textareaValues = $this->givenLocalizableTextareaValues('a_textarea');
        $notLocalizableMainTitleTextValues = $this->givenNotLocalizableMainTitleTextValues('text_main_title');

        $productValues = (new ProductValuesCollection())
            ->add($localizableTextValues1)
            ->add($localizableTextValues2)
            ->add($notLocalizableTextValues)
            ->add($textareaValues)
            ->add($notLocalizableMainTitleTextValues);

        $this->getMainTitleValues($productValues)->shouldBeLike($notLocalizableMainTitleTextValues);
    }

    public function it_returns_the_main_title_localizable_text_values_when_multiple_locales_are_activated($allActivatedLocalesQuery)
    {
        $allActivatedLocalesQuery->execute()->willReturn(new LocaleCollection([new LocaleCode('en_US'), new LocaleCode('fr_FR')]));

        $localizableTextValues1 = $this->givenLocalizableTextValues('localizable_text_1');
        $localizableTextValues2 = $this->givenLocalizableTextValues('localizable_text_2');
        $notLocalizableTextValues = $this->givenNotLocalizableTextValues('not_localizable_text');
        $textareaValues = $this->givenLocalizableTextareaValues('a_textarea');
        $notLocalizableMainTitleTextValues = $this->givenNotLocalizableMainTitleTextValues('text_main_title_not_localizable');
        $localizableMainTitleTextValues = $this->givenLocalizableMainTitleTextValues('text_main_title_localizable');

        $productValues = (new ProductValuesCollection())
            ->add($localizableTextValues1)
            ->add($localizableTextValues2)
            ->add($notLocalizableTextValues)
            ->add($textareaValues)
            ->add($localizableMainTitleTextValues)
            ->add($notLocalizableMainTitleTextValues);

        $this->getMainTitleValues($productValues)->shouldBeLike($localizableMainTitleTextValues);
    }

    private function givenLocalizableTextValues(string $attributeCode): ProductValues
    {
        $attribute = new Attribute(new AttributeCode($attributeCode), AttributeType::text(), true, false);
        $values = $this->createMinimalValues($attributeCode);

        return new ProductValues($attribute, $values);
    }

    private function givenNotLocalizableTextValues(string $attributeCode): ProductValues
    {
        $attribute = new Attribute(new AttributeCode($attributeCode), AttributeType::text(), false, false);
        $values = $this->createMinimalValues($attributeCode);

        return new ProductValues($attribute, $values);
    }

    private function givenNotLocalizableMainTitleTextValues(string $attributeCode): ProductValues
    {
        $attribute = new Attribute(new AttributeCode($attributeCode), AttributeType::text(), false, true);
        $values = $this->createMinimalValues($attributeCode);

        return new ProductValues($attribute, $values);
    }

    private function givenLocalizableMainTitleTextValues(string $attributeCode): ProductValues
    {
        $attribute = new Attribute(new AttributeCode($attributeCode), AttributeType::text(), true, true);
        $values = $this->createMinimalValues($attributeCode);

        return new ProductValues($attribute, $values);
    }

    private function givenLocalizableTextareaValues(string $attributeCode): ProductValues
    {
        $attribute = new Attribute(new AttributeCode($attributeCode), AttributeType::textarea(), true, false);
        $values = $this->createMinimalValues($attributeCode);

        return new ProductValues($attribute, $values);
    }

    private function createMinimalValues(string $value): ChannelLocaleDataCollection
    {
        return ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => $value,
            ],
        ], function ($value) { return $value; });
    }
}
