<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class IdentifierGenerator
{
    public function __construct(
        private IdentifierGeneratorCode $identifierGeneratorCode,
        private Conditions $conditions,
        private Structure $structure,
        private LabelCollection $labelCollection,
        private ?Delimiter $delimiter,
        private Target $target,
    )
    {
    }
}
