<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Domain\Model\Operation;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanReplacementOperation implements OperationInterface
{
    public const TYPE = 'boolean_replacement';

    public function __construct(
        private array $mapping,
    ) {
    }

    public function getMapping(): array
    {
        return $this->mapping;
    }

    public function normalize(): array
    {
        return [
            'type' => self::TYPE,
            'patterns' => $this->mapping,
        ];
    }
}
