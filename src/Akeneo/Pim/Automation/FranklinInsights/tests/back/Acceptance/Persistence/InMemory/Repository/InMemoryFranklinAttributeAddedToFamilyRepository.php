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

use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeAddedToFamily;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository\FranklinAttributeAddedToFamilyRepositoryInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class InMemoryFranklinAttributeAddedToFamilyRepository implements FranklinAttributeAddedToFamilyRepositoryInterface
{
    private $events = [];

    public function save(FranklinAttributeAddedToFamily $franklinAttributeAddedToFamily): void
    {
        $this->events[] = $franklinAttributeAddedToFamily;
    }
}
