<?php

declare(strict_types=1);

namespace spec\Akeneo\Tool\Component\Localization\Presenter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\AssociationColumnsResolver;
use Akeneo\Pim\Enrichment\Component\Product\Query\FindIdentifier;
use Akeneo\Tool\Component\Localization\Presenter\ProductQuantifiedAssociationPresenter;
use PhpSpec\ObjectBehavior;
use Ramsey\Uuid\Uuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductQuantifiedAssociationPresenterSpec extends ObjectBehavior
{
    public function let(FindIdentifier $findIdentifier, AssociationColumnsResolver $associationColumnsResolver): void
    {
        $associationColumnsResolver->resolveQuantifiedIdentifierAssociationColumns()->willReturn([
            'QUANTIFIED-products',
            'QUANTIFIED-product_models',
            'PACKAGE-products',
            'PACKAGE-product_models',
        ]);

        $this->beConstructedWith($findIdentifier, $associationColumnsResolver);
    }

    public function it_is_initializable(): void
    {
        $this->shouldHaveType(ProductQuantifiedAssociationPresenter::class);
    }

    public function it_only_supports_product_association(): void
    {
        $this->supports('QUANTIFIED-products')->shouldBe(true);
        $this->supports('QUANTIFIED-product_models')->shouldBe(false);
        $this->supports('PACKAGE-products')->shouldBe(true);
        $this->supports('X_SELL-products')->shouldBe(false);
        $this->supports('name-en_US')->shouldBe(false);
    }

    public function it_presents_identifier_when_product_uuid_is_passed(
        FindIdentifier $findIdentifier
    ): void {
        $uuid = Uuid::uuid4()->toString();
        $findIdentifier
            ->fromUuids([$uuid])
            ->shouldBeCalledOnce()
            ->willReturn([$uuid => 'my-identifier']);

        $value = implode(',', [$uuid]);
        $this->present($value)->shouldReturn('my-identifier');
    }

    public function it_presents_uuid_when_product_uuid_is_passed_and_product_has_no_identifier(
        FindIdentifier $findIdentifier
    ): void {
        $uuid = Uuid::uuid4()->toString();
        $findIdentifier
            ->fromUuids([$uuid])
            ->shouldBeCalledOnce()
            ->willReturn([$uuid => null]);

        $value = implode(',', [$uuid]);
        $this->present($value)->shouldReturn(sprintf('[%s]', $uuid));
    }

    public function it_presents_identifiers_when_products_uuids_are_passed(
        FindIdentifier $findIdentifier
    ): void {
        $uuid = Uuid::uuid4()->toString();
        $uuid2 = Uuid::uuid4()->toString();
        $findIdentifier
            ->fromUuids([$uuid, $uuid2])
            ->shouldBeCalledOnce()
            ->willReturn([$uuid => 'my-identifier', $uuid2 => 'my-identifier-2']);

        $value = implode(',', [$uuid, $uuid2]);
        $this->present($value)->shouldReturn('my-identifier,my-identifier-2');
    }

    public function it_presents_input_string_when_is_invalid(
        FindIdentifier $findIdentifier
    ): void {
        $findIdentifier
            ->fromUuids([])
            ->shouldBeCalledOnce()
            ->willReturn([]);

        $value = 'invalid_uuid';
        $this->present($value)->shouldReturn('invalid_uuid');
    }

    public function it_presents_uuid_when_product_is_not_found(
        FindIdentifier $findIdentifier
    ): void {
        $uuid = Uuid::uuid4()->toString();
        $findIdentifier
            ->fromUuids([$uuid])
            ->shouldBeCalledOnce()
            ->willReturn([]);

        $this->present($uuid)->shouldReturn(sprintf('[%s]', $uuid));
    }

    public function it_presents_mixed_result_when_several_products_are_finded(
        FindIdentifier $findIdentifier
    ): void {
        $uuid = Uuid::uuid4()->toString();
        $uuid2 = Uuid::uuid4()->toString();
        $uuid3 = Uuid::uuid4()->toString();
        $uuid4 = 'invalid_uuid';
        $findIdentifier
            ->fromUuids([$uuid, $uuid2, $uuid3])
            ->shouldBeCalledOnce()
            ->willReturn([$uuid => 'my-identifier', $uuid2 => null]);

        $value = implode(',', [$uuid, $uuid2, $uuid3, $uuid4]);
        $this->present($value)->shouldReturn(sprintf('%s,[%s],[%s],%s', 'my-identifier', $uuid2, $uuid3, $uuid4));
    }
}
