<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Attribute;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\IsAttributeEditable;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InMemoryIsAttributeEditable implements IsAttributeEditable
{
    /** @var array<int, string[]> */
    private $notEditableAttributeCodesPerUser = [];

    /**
     * {@inheritDoc}
     */
    public function forCode(string $attributeCode, int $userId): bool
    {
        return !\in_array($attributeCode, $this->notEditableAttributeCodesPerUser[$userId] ?? []);
    }

    public function addNotEditableAttributeForUser(string $attributeCode, int $userId): void
    {
        $this->notEditableAttributeCodesPerUser[$userId][] = $attributeCode;
    }
}
