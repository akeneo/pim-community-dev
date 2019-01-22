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

namespace Akeneo\ReferenceEntity\Application\Record\DeleteAllRecords;

/**
 * Command used to delete all records belonging to a reference entity
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class DeleteAllReferenceEntityRecordsCommand
{
    /** @var string */
    private $referenceEntityIdentifier;

    public function __construct(string $referenceEntityIdentifier)
    {
        $this->referenceEntityIdentifier = $referenceEntityIdentifier;
    }

    public function getReferenceEntityIdentifier(): string
    {
        return $this->referenceEntityIdentifier;
    }
}
