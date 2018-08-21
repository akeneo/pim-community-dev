<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\SuggestData\Infrastructure\Query\Doctrine;

use Akeneo\Pim\Automation\SuggestData\Domain\Query\GetSubscriptionStatusForProductInterface;
use Akeneo\Pim\Automation\SuggestData\Infrastructure\Query\Doctrine\GetSubscriptionStatusForProduct;
use Doctrine\DBAL\Driver\Connection;
use PhpSpec\ObjectBehavior;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class GetSubscriptionStatusForProductSpec extends ObjectBehavior
{
    function let(Connection $connection)
    {
        $this->beConstructedWith($connection);
    }

    function it_is_a_product_subscription_status_query() {
        $this->shouldImplement(GetSubscriptionStatusForProductInterface::class);
    }

    function it_is_a_doctrine_implementation_of_the_product_subscription_status_query()
    {
        $this->shouldBeAnInstanceOf(GetSubscriptionStatusForProduct::class);
    }
}
