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

namespace Akeneo\Test\Pim\Automation\FranklinInsights\Acceptance\Persistence\InMemory\Repository;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeCreated;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository\FranklinAttributeCreatedRepositoryInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class InMemoryFranklinAttributeCreatedRepository implements FranklinAttributeCreatedRepositoryInterface
{
    private $events = [];

    public function save(FranklinAttributeCreated $franklinAttributeCreated): void
    {
        $this->events[] = $franklinAttributeCreated;
    }

    public function count(): int
    {
        return count($this->events);
    }
}
