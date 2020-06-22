<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Query;

use Akeneo\ReferenceEntity\Domain\Model\Record\RecordCode;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;

/**
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 */
interface RecordIsUsedAsProductVariantAxisInterface
{
    public function execute(
        RecordCode $recordCode,
        ReferenceEntityIdentifier $referenceEntityIdentifier
    ): bool;
}
