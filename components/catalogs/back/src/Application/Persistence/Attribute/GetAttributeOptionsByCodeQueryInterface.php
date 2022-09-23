<?php
declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\Attribute;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetAttributeOptionsByCodeQueryInterface
{
    /**
     * @param array<string> $codes
     * @return array<array{code: string, label: string}>
     */
    public function execute(string $attribute, array $codes, string $locale = 'en_US'): array;
}
