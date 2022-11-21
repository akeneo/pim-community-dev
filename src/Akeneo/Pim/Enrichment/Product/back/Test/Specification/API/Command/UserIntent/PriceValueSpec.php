<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Enrichment\Product\API\Command\UserIntent;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\PriceValue;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PriceValueSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith('100', 'EUR');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType(PriceValue::class);
    }

    public function it_returns_the_amount()
    {
        $this->amount()->shouldReturn('100');
    }

    public function it_returns_the_currency()
    {
        $this->currency()->shouldReturn('EUR');
    }
}
