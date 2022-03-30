<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociationUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\ReplaceAssociatedProducts;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReplaceAssociatedProductsSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('X_SELL', ['identifier1', 'identifier2']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReplaceAssociatedProducts::class);
        $this->shouldImplement(AssociationUserIntent::class);
    }

    function it_returns_the_association_type()
    {
        $this->associationType()->shouldReturn('X_SELL');
    }

    function it_returns_the_product_identifiers()
    {
        $this->productIdentifiers()->shouldReturn(['identifier1', 'identifier2']);
    }

    function it_can_only_be_instantiated_with_string_product_identifiers()
    {
        $this->beConstructedWith('X_SELL', ['test', 12, false]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_can_be_instantiated_with_empty_product_identifiers()
    {
        $this->beConstructedWith('X_SELL', []);
        $this->productIdentifiers()->shouldReturn([]);
    }

    function it_cannot_be_instantiated_if_one_of_the_product_identifiers_is_empty()
    {
        $this->beConstructedWith('X_SELL', ['a', '', 'b']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
