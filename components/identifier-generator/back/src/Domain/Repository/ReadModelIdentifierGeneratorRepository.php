<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ReadModelIdentifierGenerator;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ReadModelIdentifierGeneratorRepository
{
    public function get(string $identifierGeneratorCode): ReadModelIdentifierGenerator;
}
