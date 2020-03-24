<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter;

/**
 * Resolve identifier field, attribute columns and flat scopable and localizable attribute name
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeColumnsResolverInterface
{
    public function resolveIdentifierField(): string;

    public function resolveAttributeColumns(): array;

    public function resolveFlatAttributeName(string $attributeCode, ?string $localeCode, ?string $scopeCode): string;
}
