<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedAssociationUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\QuantifiedEntity;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\QuantifiedAssociation\ReplaceAssociatedQuantifiedProductModels;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReplaceAssociatedQuantifiedProductModelsSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('X_SELL', [new QuantifiedEntity('foo', 5)]);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(ReplaceAssociatedQuantifiedProductModels::class);
        $this->shouldImplement(QuantifiedAssociationUserIntent::class);
        $this->shouldImplement(UserIntent::class);
    }

    public function it_cannot_be_constructed_with_empty_association_type()
    {
        $this->beConstructedWith('', [new QuantifiedEntity('foo', 5)]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cannot_be_constructed_with_non_valid_quantified_product_models()
    {
        $this->beConstructedWith('X_SELL', [new \stdClass()]);

        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_returns_the_association_type()
    {
        $this->associationType()->shouldReturn('X_SELL');
    }

    public function it_returns_the_quantified_product_models()
    {
        $this->quantifiedProductModels()->shouldBeLike([new QuantifiedEntity('foo', 5)]);
    }

    public function it_can_be_constructed_with_empty_quantified_product_models()
    {
        $this->beConstructedWith('X_SELL', []);

        $this->quantifiedProductModels()->shouldReturn([]);
    }
}
