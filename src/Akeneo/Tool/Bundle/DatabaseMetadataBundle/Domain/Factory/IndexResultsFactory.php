<?php

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Factory;

use DateTime;

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
final class IndexResultsFactory
{
    public static function initIndexDateResults(string $identifier, ?DateTime $dateTime): array
    {
        return ['identifier' => $identifier, 'date' => $dateTime];
    }
}
