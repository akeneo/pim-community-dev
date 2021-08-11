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

use Akeneo\Platform\TailoredExport\Domain\SourceValue\SimpleAssociationsValue;
use PhpSpec\ObjectBehavior;

class SimpleAssociationsValueSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(
            ['product_id_1', 'product_id_2', 'product_id_3'],
            ['product_model_code_1', 'product_model_code_2', 'product_model_code_3'],
            ['group_code_1', 'group_code_2', 'group_code_3']
        );
    }

    public function it_is_initializable(): void
    {
        $this->shouldBeAnInstanceOf(SimpleAssociationsValue::class);
    }

    public function it_throws_an_exception_if_associated_product_identifiers_are_invalid()
    {
        $this->beConstructedWith(
            ['product_id_1', 123, 'product_id_3'],
            ['product_model_code_1', 'product_model_code_2', 'product_model_code_3'],
            ['group_code_1', 'group_code_2', 'group_code_3']
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_associated_product_model_codes_are_invalid()
    {
        $this->beConstructedWith(
            ['product_id_1', 'product_id_2', 'product_id_3'],
            ['product_model_code_1', 2, 'product_model_code_3'],
            ['group_code_1', 'group_code_2', 'group_code_3']
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_throws_an_exception_if_associated_group_codes_are_invalid()
    {
        $this->beConstructedWith(
            ['product_id_1', 'product_id_2', 'product_id_3'],
            ['product_model_code_1', 'product_model_code_2', 'product_model_code_3'],
            ['group_code_1', 'group_code_2', new \stdClass()]
        );
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_the_associated_product_identifiers()
    {
        $this->getAssociatedProductIdentifiers()->shouldReturn(['product_id_1', 'product_id_2', 'product_id_3']);
    }

    public function it_returns_the_associated_product_model_codes()
    {
        $this->getAssociatedProductModelCodes()->shouldReturn(['product_model_code_1', 'product_model_code_2', 'product_model_code_3']);
    }

    public function it_returns_the_associated_group_codes()
    {
        $this->getAssociatedGroupCodes()->shouldReturn(['group_code_1', 'group_code_2', 'group_code_3']);
    }
}
