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

namespace Akeneo\ReferenceEntity\Domain\Query\Record;

use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

/**
 * Find labels for given records
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
interface FindRecordLabelsByCodesInterface
{
    /**
     * Find records by their $referenceEntityIdentifier and their $codes,
     * then returns their labels as LabelCollection indexed by their code:
     *
     * [
     *      'starck' => LabelCollection,
     *      'dyson' => LabelCollection,
     * ]
     */
    public function find(ReferenceEntityIdentifier $referenceEntityIdentifier, array $recordCodes): array;
}
