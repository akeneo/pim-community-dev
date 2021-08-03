<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Platform\TailoredExport\Domain\SourceValue;

use Akeneo\Platform\TailoredExport\Domain\SourceValue\QuantifiedAssociation;
use Akeneo\Platform\TailoredExport\Domain\SourceValue\QuantifiedAssociationsValue;
use PhpSpec\ObjectBehavior;

class QuantifiedAssociationsValueSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            [
                new QuantifiedAssociation('product_id_1', 10),
                new QuantifiedAssociation('product_id_2', 30),
            ],
            [
                new QuantifiedAssociation('product_model_id_1', 1),
                new QuantifiedAssociation('product_model_id_2', 3),
            ]
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(QuantifiedAssociationsValue::class);
    }

    public function it_throws_an_exception_if_product_associations_are_invalid()
    {
        $this->beConstructedWith(
            [
                new QuantifiedAssociation('product_id_1', 10),
                'not_a_quantified_association',
                new QuantifiedAssociation('product_id_2', 30),
            ],
            [
                new QuantifiedAssociation('product_model_id_1', 1),
                new QuantifiedAssociation('product_model_id_2', 3),
            ]
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_product_model_associations_are_invalid()
    {
        $this->beConstructedWith(
            [
                new QuantifiedAssociation('product_id_1', 10),
                new QuantifiedAssociation('product_id_2', 30),
            ],
            [
                new QuantifiedAssociation('product_model_id_1', 1),
                'not_a_quantified_association',
                new QuantifiedAssociation('product_model_id_2', 3),
            ]
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_the_associated_product_identifiers()
    {
        $this->getAssociatedProductIdentifiers()->shouldReturn(['product_id_1', 'product_id_2']);
    }

    public function it_returns_the_associated_product_model_codes()
    {
        $this->getAssociatedProductModelCodes()->shouldReturn(['product_model_id_1', 'product_model_id_2']);
    }

    public function it_returns_the_associated_product_quantities()
    {
        $this->getAssociatedProductQuantities()->shouldReturn([10, 30]);
    }

    public function it_returns_the_associated_product_model_quantities()
    {
        $this->getAssociatedProductModelQuantities()->shouldReturn([1, 3]);
    }
}
