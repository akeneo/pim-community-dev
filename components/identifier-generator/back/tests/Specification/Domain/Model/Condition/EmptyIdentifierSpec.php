<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ConditionInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\EmptyIdentifier;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EmptyIdentifierSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('sku');
    }

    public function it_is_a_condition()
    {
        $this->shouldBeAnInstanceOf(EmptyIdentifier::class);
        $this->shouldImplement(ConditionInterface::class);
    }

    public function it_should_match_product_without_identifier()
    {
        $this->match(new ProductProjection(true, null, [], []))->shouldReturn(true);
    }

    public function it_should_match_product_with_empty_identifier()
    {
        $this->match(new ProductProjection(true, null, [
            'sku-<all_channels>-<all_locales>' => ''
        ], []))->shouldReturn(true);
    }

    public function it_should_not_match_product_with_filled_identifier()
    {
        $this->match(new ProductProjection(true, null, [
            'sku-<all_channels>-<all_locales>' => 'productidentifier'
        ], []))->shouldReturn(false);
    }
}
