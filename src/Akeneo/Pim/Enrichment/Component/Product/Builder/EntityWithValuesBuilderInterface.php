<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Builder;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface EntityWithValuesBuilderInterface
{
    public function addOrReplaceValue(
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        ?string $localeCode,
        ?string $scopeCode,
        $data
    ): ?ValueInterface;
}
