<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ConditionInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type AutoNumberNormalized from AutoNumber
 * @phpstan-import-type FreeTextNormalized from FreeText
 * @phpstan-import-type FamilyPropertyNormalized from FamilyProperty
 * @phpstan-import-type SimpleSelectPropertyNormalized from SimpleSelectProperty
 * @phpstan-import-type ReferenceEntityPropertyNormalized from ReferenceEntityProperty
 * @phpstan-type PropertyNormalized AutoNumberNormalized | FreeTextNormalized | FamilyPropertyNormalized | SimpleSelectPropertyNormalized | ReferenceEntityPropertyNormalized
 */
interface PropertyInterface
{
    /**
     * @return PropertyNormalized
     */
    public function normalize(): array;

    /**
     * @param array<string, mixed> $fromNormalized
     * @return self
     */
    public static function fromNormalized(array $fromNormalized): self;

    public static function type(): string;

    public function getImplicitCondition(): ?ConditionInterface;
}
