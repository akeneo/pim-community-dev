<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GenerateIdentifierCommand
{
    private function __construct(
        private IdentifierGenerator $identifierGenerator
    ) {
    }

    public static function fromIdentifierGenerator(IdentifierGenerator $identifierGenerator): self
    {
        return new self($identifierGenerator);
    }

    public function getIdentifierGenerator(): IdentifierGenerator
    {
        return $this->identifierGenerator;
    }
}
