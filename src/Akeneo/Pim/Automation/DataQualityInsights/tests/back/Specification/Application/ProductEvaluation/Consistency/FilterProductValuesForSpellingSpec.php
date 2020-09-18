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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Application\ProductEvaluation\Consistency;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleDataCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\LocaleCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValues;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValuesCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetAllActivatedLocalesQueryInterface;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use PhpSpec\ObjectBehavior;
use Webmozart\Assert\Assert;

class FilterProductValuesForSpellingSpec extends ObjectBehavior
{
    public function let(GetAllActivatedLocalesQueryInterface $allActivatedLocalesQuery)
    {
        $this->beConstructedWith($allActivatedLocalesQuery);
    }

    public function it_returns_the_localizable_text_and_textarea_values_for_a_catalog_with_several_locales($allActivatedLocalesQuery)
    {
        $allActivatedLocalesQuery->execute()->willReturn(new LocaleCollection([new LocaleCode('en_US'), new LocaleCode('fr_FR')]));

        $localizableTextValues1 = $this->givenLocalizableTextValues('localizable_text_1');
        $localizableTextValues2 = $this->givenLocalizableTextValues('localizable_text_2');
        $localizableTextareaValues = $this->givenLocalizableTextareaValues('localizable_textarea');
        $notLocalizableTextValues = $this->givenNotLocalizableTextValues('not_localizable_text');
        $notLocalizableTextareaValues = $this->givenNotLocalizableTextareaValues('not_localizable_textarea');
        $simpleSelectValues = $this->givenLocalizableSimpleSelectValues('a_simple_select');

        $productValues = (new ProductValuesCollection())
            ->add($localizableTextValues1)
            ->add($localizableTextValues2)
            ->add($localizableTextareaValues)
            ->add($notLocalizableTextValues)
            ->add($notLocalizableTextareaValues)
            ->add($simpleSelectValues);

        $returnedValues = $this->getFilteredProductValues($productValues)->getWrappedObject();

        Assert::eq($returnedValues, [$localizableTextValues1, $localizableTextValues2, $localizableTextareaValues]);
    }

    public function it_returns_all_text_and_textarea_values_for_a_catalog_with_a_single_locale($allActivatedLocalesQuery)
    {
        $allActivatedLocalesQuery->execute()->willReturn(new LocaleCollection([new LocaleCode('en_US')]));

        $localizableTextValues = $this->givenLocalizableTextValues('localizable_text');
        $notLocalizableTextValues = $this->givenNotLocalizableTextValues('not_localizable_text');
        $localizableTextareaValues = $this->givenLocalizableTextareaValues('localizable_textarea');
        $notLocalizableTextareaValues = $this->givenNotLocalizableTextareaValues('not_localizable_textarea');
        $simpleSelectValues = $this->givenLocalizableSimpleSelectValues('a_simple_select');

        $productValues = (new ProductValuesCollection())
            ->add($localizableTextValues)
            ->add($notLocalizableTextValues)
            ->add($localizableTextareaValues)
            ->add($notLocalizableTextareaValues)
            ->add($simpleSelectValues);

        $returnedValues = $this->getFilteredProductValues($productValues)->getWrappedObject();

        Assert::eq($returnedValues, [$localizableTextValues, $notLocalizableTextValues, $localizableTextareaValues, $notLocalizableTextareaValues]);
    }

    private function givenLocalizableTextValues(string $attributeCode): ProductValues
    {
        $attribute = new Attribute(new AttributeCode($attributeCode), AttributeType::text(), true);
        $values = $this->createMinimalValues($attributeCode);

        return new ProductValues($attribute, $values);
    }

    private function givenNotLocalizableTextValues(string $attributeCode): ProductValues
    {
        $attribute = new Attribute(new AttributeCode($attributeCode), AttributeType::text(), false);
        $values = $this->createMinimalValues($attributeCode);

        return new ProductValues($attribute, $values);
    }

    private function givenLocalizableTextareaValues(string $attributeCode): ProductValues
    {
        $attribute = new Attribute(new AttributeCode($attributeCode), AttributeType::textarea(), true);
        $values = $this->createMinimalValues($attributeCode);

        return new ProductValues($attribute, $values);
    }

    private function givenNotLocalizableTextareaValues(string $attributeCode): ProductValues
    {
        $attribute = new Attribute(new AttributeCode($attributeCode), AttributeType::textarea(), false);
        $values = $this->createMinimalValues($attributeCode);

        return new ProductValues($attribute, $values);
    }

    private function givenLocalizableSimpleSelectValues(string $attributeCode): ProductValues
    {
        $attribute = new Attribute(new AttributeCode($attributeCode), AttributeType::simpleSelect(), true);
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
