<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UndefinedFamilyNomenclatureException extends \Exception
{
    public function __construct(
    ) {
        parent::__construct('You should define your Family nomenclature in the identifier generator feature to be able to generate identifiers.');
    }
}
