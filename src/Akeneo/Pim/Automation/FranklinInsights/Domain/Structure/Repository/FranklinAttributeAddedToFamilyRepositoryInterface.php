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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeAddedToFamily;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
interface FranklinAttributeAddedToFamilyRepositoryInterface
{
    public function save(FranklinAttributeAddedToFamily $franklinAttributeAddedToFamily): void;

    /**
     * @param FranklinAttributeAddedToFamily[] $franklinAttributeAddedToFamilyEvents
     */
    public function saveAll(array $franklinAttributeAddedToFamilyEvents): void;

    public function count(): int;
}
