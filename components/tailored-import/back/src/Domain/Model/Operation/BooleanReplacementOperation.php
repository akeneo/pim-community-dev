<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Domain\Model\Operation;

final class BooleanReplacementOperation implements OperationInterface
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
