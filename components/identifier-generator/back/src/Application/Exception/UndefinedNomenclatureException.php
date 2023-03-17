<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\Error;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Validation\ErrorList;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UndefinedNomenclatureException extends UnableToSetIdentifierException
{
    public function __construct(
        string $identifier,
        string $target,
        string $nomenclatureProperty,
    ) {
        parent::__construct($identifier, $target, new ErrorList([
            new Error(\sprintf(
                'You should define your %s nomenclature in the identifier generator feature to be able to generate identifiers.',
                $nomenclatureProperty
            )),
        ]));
    }
}
