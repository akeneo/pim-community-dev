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

use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Query\SelectFamilyAttributeCodesQueryInterface;
use Doctrine\DBAL\Connection;
use PhpSpec\ObjectBehavior;

class SelectFamilyAttributeCodesQuerySpec extends ObjectBehavior
{
    public function it_is_a_select_family_attribute_codes_query(Connection $connection): void
    {
        $this->beConstructedWith($connection);
        $this->shouldImplement(SelectFamilyAttributeCodesQueryInterface::class);
    }
}
