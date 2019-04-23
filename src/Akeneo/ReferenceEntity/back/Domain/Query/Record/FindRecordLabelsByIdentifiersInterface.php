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

namespace Akeneo\ReferenceEntity\Domain\Query\Record;

/**
 * Find labels for given record identifiers
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
interface FindRecordLabelsByIdentifiersInterface
{
    /**
     * Find records by their $recordIdentifiers then returns their labels by their record identifier:
     * [
     *      'designer_starck_abcdef123456789' => ['fr_FR' => 'Un label', 'en_US' => 'A label'],
     *      'designer_dyson_abcdef123456789' => ['fr_FR' => 'Un label', 'en_US' => 'A label'],
     * ]
     */
    public function find(array $recordIdentifiers): array;
}
