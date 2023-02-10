<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\Error;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\ErrorList;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UnableToGenerateIdentifierFromFamilyNomenclature extends UnableToSetIdentifierException
{
    public function __construct(
        string $identifier,
        string $target,
        string $familyCode,
    ) {
        parent::__construct($identifier, $target, new ErrorList([
            new Error(\sprintf('No mapping found for familyCode %s', $familyCode)),
        ]));
    }
}
