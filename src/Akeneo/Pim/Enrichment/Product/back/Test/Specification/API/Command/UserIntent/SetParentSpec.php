<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetParent;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SetParentSpec extends ObjectBehavior
{
    function it_can_be_constructed_with_parent_code()
    {
        $this->beConstructedWith('test_product_model');
        $this->shouldBeAnInstanceOf(SetParent::class);
        $this->parentCode()->shouldReturn('test_product_model');
    }
}
