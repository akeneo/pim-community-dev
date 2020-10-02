<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Storage;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetProductAndProductModelIdentifiersWithValuesIgnoringLocaleAndScope
{
    /**
     * Returns product and product model ids that have at least one value for the given attribute
     * no matter the locale and the scope.
     * It returns multiple array of identifiers.
     */
    public function forAttributeAndValues(string $attributeCode, string $backendType, array $values): iterable;
}
