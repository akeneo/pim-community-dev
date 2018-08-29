<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\SuggestData\Domain\Exception;

use Akeneo\Pim\Automation\SuggestData\Domain\Exception\ProductSubscriptionException;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\SuggestDataException;
use PhpSpec\ObjectBehavior;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProductSubscriptionExceptionSpec extends ObjectBehavior
{
    public function it_is_a_product_subscription_exception()
    {
        $this->shouldBeAnInstanceOf(ProductSubscriptionException::class);
    }

    public function it_is_an_exception()
    {
        $this->shouldBeAnInstanceOf(\Exception::class);
    }
}
