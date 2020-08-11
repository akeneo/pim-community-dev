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

    public function it_returns_the_localizable_text_values_for_a_catalog_with_several_locales($allActivatedLocalesQuery)
    {
        $allActivatedLocalesQuery->execute()->willReturn(new LocaleCollection([new LocaleCode('en_US'), new LocaleCode('fr_FR')]));

        $localizableTextValues1 = $this->givenLocalizableTextValues('localizable_text_1');
        $localizableTextValues2 = $this->givenLocalizableTextValues('localizable_text_2');
        $notLocalizableTextValues = $this->givenNotLocalizableTextValues('not_localizable_text');
        $textareaValues = $this->givenLocalizableTextareaValues('a_textarea');

        $productValues = (new ProductValuesCollection())
            ->add($localizableTextValues1)
            ->add($localizableTextValues2)
            ->add($notLocalizableTextValues)
            ->add($textareaValues);

        $returnedValues = $this->getTextValues($productValues)->getWrappedObject();

        Assert::eq(iterator_to_array($returnedValues), [$localizableTextValues1, $localizableTextValues2]);
    }

    public function it_returns_the_localizable_textarea_values_for_a_catalog_with_several_locales($allActivatedLocalesQuery)
    {
        $allActivatedLocalesQuery->execute()->willReturn(new LocaleCollection([new LocaleCode('en_US'), new LocaleCode('fr_FR')]));

        $localizableTextareaValues1 = $this->givenLocalizableTextareaValues('localizable_textarea_1');
        $localizableTextareaValues2 = $this->givenLocalizableTextareaValues('localizable_textarea_2');
        $notLocalizableTextareaValues = $this->givenNotLocalizableTextareaValues('not_localizable_textarea');
        $textValues = $this->givenLocalizableTextValues('a_text');

        $productValues = (new ProductValuesCollection())
            ->add($localizableTextareaValues1)
            ->add($localizableTextareaValues2)
            ->add($notLocalizableTextareaValues)
            ->add($textValues);

        $returnedValues = $this->getTextareaValues($productValues)->getWrappedObject();

        Assert::eq(iterator_to_array($returnedValues), [$localizableTextareaValues1, $localizableTextareaValues2]);
    }

    public function it_returns_all_text_values_for_a_catalog_with_a_single_locale($allActivatedLocalesQuery)
    {
        $allActivatedLocalesQuery->execute()->willReturn(new LocaleCollection([new LocaleCode('en_US')]));

        $localizableTextValues = $this->givenLocalizableTextValues('localizable_text');
        $notLocalizableTextValues = $this->givenNotLocalizableTextValues('not_localizable_text');
        $textareaValues = $this->givenLocalizableTextareaValues('a_textarea');

        $productValues = (new ProductValuesCollection())
            ->add($localizableTextValues)
            ->add($notLocalizableTextValues)
            ->add($textareaValues);

        $returnedValues = $this->getTextValues($productValues)->getWrappedObject();

        Assert::eq(iterator_to_array($returnedValues), [$localizableTextValues, $notLocalizableTextValues]);
    }

    public function it_returns_all_textarea_values_for_a_catalog_with_a_single_locale($allActivatedLocalesQuery)
    {
        $allActivatedLocalesQuery->execute()->willReturn(new LocaleCollection([new LocaleCode('en_US')]));

        $localizableTextareaValues = $this->givenLocalizableTextareaValues('localizable_textarea_1');
        $notLocalizableTextareaValues = $this->givenNotLocalizableTextareaValues('not_localizable_textarea');
        $textValues = $this->givenLocalizableTextValues('a_text');

        $productValues = (new ProductValuesCollection())
            ->add($localizableTextareaValues)
            ->add($notLocalizableTextareaValues)
            ->add($textValues);

        $returnedValues = $this->getTextareaValues($productValues)->getWrappedObject();

        Assert::eq(iterator_to_array($returnedValues), [$localizableTextareaValues, $notLocalizableTextareaValues]);
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

    private function createMinimalValues(string $value): ChannelLocaleDataCollection
    {
        return ChannelLocaleDataCollection::fromNormalizedChannelLocaleData([
            'ecommerce' => [
                'en_US' => $value,
            ],
        ], function ($value) { return $value; });
    }
}
