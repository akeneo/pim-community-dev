<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;

class ExecuteDataMappingResult
{
    public function __construct(
        private UpsertProductCommand $upsertProductCommand,
        private array $invalidValues,
    ) {
    }

    public function getUpsertProductCommand(): UpsertProductCommand
    {
        return $this->upsertProductCommand;
    }

    /**
     * @return InvalidValue[]
     */
    public function getInvalidValues(): array
    {
        return $this->invalidValues;
    }
}
