<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Model\Read\Attribute;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifierMapping;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class IdentifiersMappingSpec extends ObjectBehavior
{
    public function let(
        Attribute $manufacturer,
        Attribute $model,
        Attribute $ean,
        Attribute $sku
    ): void {
        $manufacturer->getCode()->willReturn(new AttributeCode('manufacturer'));
        $model->getCode()->willReturn(new AttributeCode('model'));
        $ean->getCode()->willReturn(new AttributeCode('ean'));
        $sku->getCode()->willReturn(new AttributeCode('sku'));

        $this->beConstructedWith(
            [
                'asin' => $sku,
                'upc' => $ean,
                'brand' => $manufacturer,
                'mpn' => $model,
            ]
        );
    }

    public function it_gets_identifiers($manufacturer, $model, $ean, $sku): void
    {
        $this->getMapping()->shouldBeLike(
            [
                'brand' => new IdentifierMapping('brand', $manufacturer->getWrappedObject()),
                'mpn' => new IdentifierMapping('mpn', $model->getWrappedObject()),
                'upc' => new IdentifierMapping('upc', $ean->getWrappedObject()),
                'asin' => new IdentifierMapping('asin', $sku->getWrappedObject()),
            ]
        );
    }

    public function it_is_valid_if_mapping_is_filled(): void
    {
        $this->isValid()->shouldReturn(true);
    }

    public function it_is_valid_if_mapping_is_filled_with_upc($ean): void
    {
        $this->beConstructedWith(['upc' => $ean]);
        $this->isValid()->shouldReturn(true);
    }

    public function it_is_valid_if_mapping_is_filled_with_asin($sku): void
    {
        $this->beConstructedWith(['asin' => $sku]);
        $this->isValid()->shouldReturn(true);
    }

    public function it_is_valid_if_mapping_is_filled_with_mpn_and_brand($manufacturer, $model): void
    {
        $this->beConstructedWith(
            [
                'brand' => $manufacturer,
                'mpn' => $model,
            ]
        );

        $this->isValid()->shouldReturn(true);
    }

    public function it_is_not_valid_if_mapping_is_not_filled(): void
    {
        $this->beConstructedWith([]);
        $this->isValid()->shouldReturn(false);
    }

    public function it_is_not_valid_if_mapping_is_filled_only_with_brand($manufacturer): void
    {
        $this->beConstructedWith(['brand' => $manufacturer]);

        $this->isValid()->shouldReturn(false);
    }

    public function it_is_not_valid_if_mapping_is_filled_only_with_mpn($model): void
    {
        $this->beConstructedWith(['mpn' => $model]);

        $this->isValid()->shouldReturn(false);
    }

    public function it_is_traversable(): void
    {
        $this->shouldHaveType(\Traversable::class);

        $this->getIterator()->shouldReturnAnInstanceOf(\Iterator::class);
    }

    public function it_can_check_if_mapping_is_defined($sku): void
    {
        $this->beConstructedWith([]);
        $this->isEmpty()->shouldReturn(true);

        $this->map('asin', $sku->getWrappedObject());
        $this->isEmpty()->shouldReturn(false);

        $this->map('asin', null);
        $this->isEmpty()->shouldReturn(true);
    }

    public function it_can_tell_if_it_has_been_updated(Attribute $asin): void
    {
        $this->isUpdated()->shouldReturn(false);
        $asin->getCode()->willReturn(new AttributeCode('asin'));
        $this->map('asin', $asin->getWrappedObject());
        $this->isUpdated()->shouldReturn(true);
    }

    public function it_is_not_updated_when_the_same_attribute_is_mapped($ean): void
    {
        $this->map('upc', $ean->getWrappedObject());
        $this->isUpdated()->shouldReturn(false);
    }

    public function it_provides_the_identifier_codes_with_an_updated_or_removed_mapping(
        Attribute $asin
    ): void {
        $asin->getCode()->willReturn(new AttributeCode('asin'));
        $this->map('asin', $asin->getWrappedObject());
        $this->map('upc', null);
        $this->updatedIdentifierCodes()->shouldReturn(['asin', 'upc']);
    }

    public function it_does_not_provide_the_identifier_codes_with_an_added_mapping($sku, $ean): void
    {
        $this->beConstructedWith(['asin' => $sku]);
        $this->map('upc', $ean->getWrappedObject());
        $this->isUpdated()->shouldReturn(true);
        $this->updatedIdentifierCodes()->shouldReturn([]);
    }

    public function it_finds_if_attribute_is_mapped_to_an_identifier($sku, Attribute $attribute): void
    {
        $attribute->getCode()->willReturn(new AttributeCode('test'));
        $this->isMappedTo($sku->getWrappedObject()->getCode())->shouldReturn(true);
        $this->isMappedTo($attribute->getWrappedObject()->getCode())->shouldReturn(false);
    }
}
