<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\TailoredImport\Domain\Model\Operation;

final class ReferenceEntitySingleLinkReplacementOperation extends AbstractReplacementOperation
{
    public const TYPE = 'reference_entity_single_link_replacement';

    public function normalize(): array
    {
        return [
            'type' => self::TYPE,
            'uuid' => $this->uuid,
            'mapping' => $this->getMapping()
        ];
    }
}
