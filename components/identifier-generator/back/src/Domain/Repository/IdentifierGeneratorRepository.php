<?php
declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface IdentifierGeneratorRepository
{
    public function save(IdentifierGenerator $identifierGenerator): void;

    public function get(string $identifierGeneratorCode): ?IdentifierGenerator;
}
