<?php
declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Asset\EnrichmentComponent;

use Akeneo\Pim\Enrichment\Component\Product\Factory\Read\Value\ValueFactory;
use Akeneo\Pim\Enrichment\Component\Product\Value\ReferenceDataCollectionValue;
use Akeneo\Asset\Bundle\AttributeType\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use PhpSpec\ObjectBehavior;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class ReadAssetCollectionValueFactorySpec extends ObjectBehavior
{
    public function it_is_a_read_value_factory()
    {
        $this->shouldBeAnInstanceOf(ValueFactory::class);
    }

    public function it_supports_asset_collection_attribute_type()
    {
        $this->supportedAttributeType()->shouldReturn(AttributeTypes::ASSETS_COLLECTION);
    }

    public function it_supports_null()
    {
        $attribute = $this->getAttribute(true, true);
        $value = $this->createByCheckingData($attribute, 'ecommerce', 'fr_FR', null);
        $value->shouldBeLike(ReferenceDataCollectionValue::scopableLocalizableValue('an_attribute', [], 'ecommerce', 'fr_FR'));
    }

    public function it_creates_a_localizable_and_scopable_value()
    {
        $attribute = $this->getAttribute(true, true);
        $value = $this->createWithoutCheckingData($attribute, 'ecommerce', 'fr_FR', ['michel', 'sardou']);
        $value->shouldBeLike(ReferenceDataCollectionValue::scopableLocalizableValue('an_attribute', ['michel', 'sardou'], 'ecommerce', 'fr_FR'));
    }

    public function it_creates_a_localizable_value()
    {
        $attribute = $this->getAttribute(true, false);
        $value = $this->createWithoutCheckingData($attribute, null, 'fr_FR', ['michel', 'sardou']);
        $value->shouldBeLike(ReferenceDataCollectionValue::localizableValue('an_attribute', ['michel', 'sardou'], 'fr_FR'));
    }

    public function it_creates_a_scopable_value()
    {
        $attribute = $this->getAttribute(false, true);
        $value = $this->createWithoutCheckingData($attribute, 'ecommerce', null, ['michel', 'sardou']);
        $value->shouldBeLike(ReferenceDataCollectionValue::scopableValue('an_attribute', ['michel', 'sardou'], 'ecommerce'));
    }

    public function it_creates_a_non_localizable_and_non_scopable_value()
    {
        $attribute = $this->getAttribute(false, false);
        $value = $this->createWithoutCheckingData($attribute, null, null, ['michel', 'sardou']);
        $value->shouldBeLike(ReferenceDataCollectionValue::value('an_attribute', ['michel', 'sardou']));
    }

    public function it_throws_an_exception_if_not_an_array_of_string()
    {
        $this->shouldThrow(InvalidPropertyTypeException::class)
            ->during('createByCheckingData', [
                $this->getAttribute(true, true),
                null,
                null,
                [new \stdClass()]
            ]);
    }

    private function getAttribute(bool $isLocalizable, bool $isScopable): Attribute
    {
        return new Attribute('an_attribute', AttributeTypes::ASSETS_COLLECTION, ['reference_data_name' => 'color'], $isLocalizable, $isScopable, null, false);
    }
}
