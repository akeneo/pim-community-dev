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

namespace Akeneo\ReferenceEntity\Application\Record\DeleteRecord;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class DeleteRecordCommand
{
    /** @var string */
    public $recordCode;

    /** @var string */
    public $referenceEntityIdentifier;

    public function __construct(string $recordCode, string $referenceEntityIdentifier)
    {
        $this->recordCode = $recordCode;
        $this->referenceEntityIdentifier = $referenceEntityIdentifier;
    }
}
