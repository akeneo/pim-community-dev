<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociationUserIntent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\DissociateProductModels;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DissociateProductModelsSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('X_SELL', ['code1', 'code2']);
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(DissociateProductModels::class);
        $this->shouldImplement(AssociationUserIntent::class);
    }

    public function it_returns_the_association_type()
    {
        $this->associationType()->shouldReturn('X_SELL');
    }

    public function it_returns_the_product_model_codes()
    {
        $this->productModelCodes()->shouldReturn(['code1', 'code2']);
    }

    public function it_can_only_be_instantiated_with_string_product_model_codes()
    {
        $this->beConstructedWith('X_SELL', ['test', 12, false]);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cannot_be_instantiated_with_empty_product_model_codes()
    {
        $this->beConstructedWith('X_SELL', []);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cannot_be_instantiated_if_one_of_the_product_model_codes_is_empty()
    {
        $this->beConstructedWith('X_SELL', ['a', '', 'b']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }

    public function it_cannot_be_instantiated_with_empty_association_type()
    {
        $this->beConstructedWith('', ['code1', 'code2']);
        $this->shouldThrow(\InvalidArgumentException::class)->duringInstantiation();
    }
}
