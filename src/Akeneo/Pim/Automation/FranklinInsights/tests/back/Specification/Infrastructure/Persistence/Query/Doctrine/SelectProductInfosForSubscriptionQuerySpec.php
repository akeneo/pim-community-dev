<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\SelectProductIdentifierValuesQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Query\Product\SelectProductInfosForSubscriptionQueryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\Doctrine\SelectProductInfosForSubscriptionQuery;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;

class SelectProductInfosForSubscriptionQuerySpec extends ObjectBehavior
{
    public function let(
        Connection $connection,
        FamilyRepositoryInterface $familyRepository,
        SelectProductIdentifierValuesQueryInterface $selectProductIdentifierValuesQuery
    ) {
        $this->beConstructedWith($connection, $familyRepository, $selectProductIdentifierValuesQuery);
    }

    public function it_is_a_query_to_select_product_info_for_subscription()
    {
        $this->shouldImplement(SelectProductInfosForSubscriptionQueryInterface::class);
    }

    public function it_is_the_doctrine_implementation_of_the_query()
    {
        $this->shouldHaveType(SelectProductInfosForSubscriptionQuery::class);
    }
}
