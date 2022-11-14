<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductUuids;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReplaceAssociatedQuantifiedProductUuidsSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith('PRODUCT_SET', [new QuantifiedEntity('b8f895c5-330a-4d6d-9a74-78db307633bd', 5)]);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ReplaceAssociatedQuantifiedProductUuids::class);
        $this->shouldImplement(QuantifiedAssociationUserIntent::class);
        $this->shouldImplement(UserIntent::class);
    }

    function it_cannot_be_constructed_with_empty_association_type()
    {
        $this->beConstructedWith('', [new QuantifiedEntity('b8f895c5-330a-4d6d-9a74-78db307633bd', 5)]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_cannot_be_constructed_with_non_valid_quantified_products()
    {
        $this->beConstructedWith('PRODUCT_SET', [new \stdClass()]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    function it_returns_the_association_type()
    {
        $this->associationType()->shouldReturn('PRODUCT_SET');
    }

    function it_returns_the_quantified_products()
    {
        $this->quantifiedProducts()->shouldBeLike([new QuantifiedEntity('b8f895c5-330a-4d6d-9a74-78db307633bd', 5)]);
    }

    function it_can_be_constructed_with_empty_quantified_products()
    {
        $this->beConstructedWith('PRODUCT_SET', []);

        $this->quantifiedProducts()->shouldReturn([]);
    }
}
