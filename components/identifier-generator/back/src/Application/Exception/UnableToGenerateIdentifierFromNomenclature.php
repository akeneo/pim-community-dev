<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\Error;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\ErrorList;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UnableToGenerateIdentifierFromNomenclature extends UnableToSetIdentifierException
{
    public function __construct(
        string $identifier,
        string $target,
        string $familyCode,
        string $propertyCode,
    ) {
        parent::__construct($identifier, $target, new ErrorList([
            new Error(\sprintf('No mapping found for %s %s', $propertyCode, $familyCode)),
        ]));
    }
}
