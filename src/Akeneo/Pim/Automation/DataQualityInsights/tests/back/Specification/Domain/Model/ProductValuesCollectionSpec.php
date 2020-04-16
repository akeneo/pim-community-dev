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

namespace Specification\Akeneo\Pim\Automation\DataQualityInsights\Domain\Model;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Attribute;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ChannelLocaleDataCollection;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\ProductValues;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeType;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ChannelCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\LocaleCode;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

class ProductValuesCollectionSpec extends ObjectBehavior
{
    public function it_returns_the_product_values_for_attributes_of_type_text()
    {
        $attributeText1 = $this->givenALocalizableAttributeOfTypeText('text_1');
        $attributeText2 = $this->givenANotLocalizableAttributeOfTypeText('text_2');
        $attributeTextarea = $this->givenALocalizableAttributeOfTypeTextarea('a_textarea');

        $textValues1 = $this->givenRandomValuesForAttribute($attributeText1);
        $textValues2 = $this->givenRandomValuesForAttribute($attributeText2);
        $textareaValues = $this->givenRandomValuesForAttribute($attributeTextarea);

        $this->add($textValues1);
        $this->add($textValues2);
        $this->add($textareaValues);

        $allTextValues = iterator_to_array($this->getTextValues()->getWrappedObject());
        Assert::eq($allTextValues, [$textValues1, $textValues2]);
    }

    public function it_returns_the_product_values_for_attributes_of_type_textarea()
    {
        $attributeTextarea1 = $this->givenALocalizableAttributeOfTypeTextarea('a_textarea_1');
        $attributeTextarea2 = $this->givenANotLocalizableAttributeOfTypeTextarea('a_textarea_2');
        $attributeText = $this->givenALocalizableAttributeOfTypeText('a_text');

        $textareaValues1 = $this->givenRandomValuesForAttribute($attributeTextarea1);
        $textareaValues2 = $this->givenRandomValuesForAttribute($attributeTextarea2);
        $textValues = $this->givenRandomValuesForAttribute($attributeText);

        $this->add($textareaValues1);
        $this->add($textareaValues2);
        $this->add($textValues);

        $allTextValues = iterator_to_array($this->getTextareaValues()->getWrappedObject());
        Assert::eq($allTextValues, [$textareaValues1, $textareaValues2]);
    }

    public function it_returns_the_product_values_for_localizable_attributes_of_type_text()
    {
        $localizableAttributeText1 = $this->givenALocalizableAttributeOfTypeText('localizable_text_1');
        $localizableAttributeText2 = $this->givenALocalizableAttributeOfTypeText('localizable_text_2');
        $notLocalizableAttributeText = $this->givenANotLocalizableAttributeOfTypeText('not_localizable_text');
        $attributeTextarea = $this->givenALocalizableAttributeOfTypeTextarea('a_textarea');

        $localizableTextValues1 = $this->givenRandomValuesForAttribute($localizableAttributeText1);
        $localizableTextValues2 = $this->givenRandomValuesForAttribute($localizableAttributeText2);
        $notLocalizableTextValues = $this->givenRandomValuesForAttribute($notLocalizableAttributeText);
        $textareaValues = $this->givenRandomValuesForAttribute($attributeTextarea);

        $this->add($localizableTextValues1);
        $this->add($localizableTextValues2);
        $this->add($notLocalizableTextValues);
        $this->add($textareaValues);

        $allTextValues = iterator_to_array($this->getLocalizableTextValues()->getWrappedObject());
        Assert::eq($allTextValues, [$localizableTextValues1, $localizableTextValues2]);
    }

    public function it_returns_the_product_values_for_localizable_attributes_of_type_textarea()
    {
        $localizableAttributeTextarea1 = $this->givenALocalizableAttributeOfTypeTextarea('localizable_textarea_1');
        $localizableAttributeTextarea2 = $this->givenALocalizableAttributeOfTypeTextarea('localizable_textarea_2');
        $notLocalizableAttributeText = $this->givenANotLocalizableAttributeOfTypeTextarea('not_localizable_textarea');
        $attributeText = $this->givenALocalizableAttributeOfTypeText('a_text');

        $localizableTextareaValues1 = $this->givenRandomValuesForAttribute($localizableAttributeTextarea1);
        $localizableTextareaValues2 = $this->givenRandomValuesForAttribute($localizableAttributeTextarea2);
        $notLocalizableTextareaValues = $this->givenRandomValuesForAttribute($notLocalizableAttributeText);
        $textValues = $this->givenRandomValuesForAttribute($attributeText);

        $this->add($localizableTextareaValues1);
        $this->add($localizableTextareaValues2);
        $this->add($notLocalizableTextareaValues);
        $this->add($textValues);

        $allTextValues = iterator_to_array($this->getLocalizableTextareaValues()->getWrappedObject());
        Assert::eq($allTextValues, [$localizableTextareaValues1, $localizableTextareaValues2]);
    }

    public function it_returns_the_values_for_the_localizable_main_title()
    {
        $commonTextAttribute = $this->givenALocalizableAttributeOfTypeText('a_text');
        $mainTitleAttribute = $this->givenALocalizableMainTitleAttribute('main_title');

        $commonTextValues = $this->givenRandomValuesForAttribute($commonTextAttribute);
        $mainTitleValues = $this->givenRandomValuesForAttribute($mainTitleAttribute);

        $this->add($commonTextValues);
        $this->add($mainTitleValues);

        $this->getLocalizableMainTitleValues()->shouldReturn($mainTitleValues);
    }

    public function it_returns_null_if_there_is_no_main_title()
    {
        $commonTextAttribute = $this->givenALocalizableAttributeOfTypeText('a_text');
        $commonTextValues = $this->givenRandomValuesForAttribute($commonTextAttribute);

        $this->add($commonTextValues);

        $this->getLocalizableMainTitleValues()->shouldReturn(null);
    }

    public function it_returns_null_if_the_main_title_is_not_localizable()
    {
        $notLocalizableMainTitleAttribute = $this->givenANotLocalizableMainTitleAttribute('not_localizable_main_title');
        $mainTitleValues = $this->givenRandomValuesForAttribute($notLocalizableMainTitleAttribute);

        $this->add($mainTitleValues);

        $this->getLocalizableMainTitleValues()->shouldReturn(null);
    }

    private function givenALocalizableAttributeOfTypeText(string $code): Attribute
    {
        return new Attribute(new AttributeCode($code), AttributeType::text(), true, false);
    }

    private function givenANotLocalizableAttributeOfTypeText(string $code): Attribute
    {
        return new Attribute(new AttributeCode($code), AttributeType::text(), false, false);
    }

    private function givenALocalizableAttributeOfTypeTextarea(string $code): Attribute
    {
        return new Attribute(new AttributeCode($code), AttributeType::textarea(), true, false);
    }

    private function givenANotLocalizableAttributeOfTypeTextarea(string $code): Attribute
    {
        return new Attribute(new AttributeCode($code), AttributeType::textarea(), false, false);
    }

    private function givenALocalizableMainTitleAttribute(string $code): Attribute
    {
        return new Attribute(new AttributeCode($code), AttributeType::text(), true, true);
    }

    private function givenANotLocalizableMainTitleAttribute(string $code): Attribute
    {
        return new Attribute(new AttributeCode($code), AttributeType::text(), false, true);
    }

    private function givenRandomValuesForAttribute(Attribute $attribute): ProductValues
    {
        $values = (new ChannelLocaleDataCollection())
            ->addToChannelAndLocale(new ChannelCode('mobile'), new LocaleCode('en_US'), strval(Uuid::uuid4()))
            ->addToChannelAndLocale(new ChannelCode('print'), new LocaleCode('fr_FR'), strval(Uuid::uuid4()));

        return new ProductValues($attribute, $values);
    }
}
