<?php
declare(strict_types=1);

namespace AkeneoTest\Acceptance\Attribute;

use Akeneo\Pim\Structure\Component\Query\InternalApi\IsAttributeCodeBlacklistedInterface;

final class InMemoryIsAttributeCodeBlacklisted implements IsAttributeCodeBlacklistedInterface
{
    private array $resultPerAttributeCode = [];

    public function execute(string $attributeCode): bool
    {
        return $this->resultPerAttributeCode[$attributeCode] ?? false;
    }

    public function setResultForAttributeCode(string $attributeCode, bool $result): void
    {
        $this->resultPerAttributeCode[$attributeCode] = $result;
    }
}
