<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface PropertyInterface
{
    /**
     * @return array<string, int|string>
     */
    public function normalize(): array;

    /**
     * @param array<string, int|string> $fromNormalized
     * @return self
     */
    public static function fromNormalized(array $fromNormalized): self;

    public static function type(): string;
}
