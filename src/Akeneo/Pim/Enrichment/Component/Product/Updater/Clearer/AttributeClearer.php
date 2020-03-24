<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;

/**
 * Clear the attribute value of an entity.
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributeClearer implements AttributeClearerInterface
{
    public function supportsAttribute(Attribute $attribute): bool
    {
        return true;
    }

    public function clear(EntityWithValuesInterface $entity, Attribute $attribute, array $options = []): void
    {
        $value = $entity->getValue($attribute->code(), $options['locale'] ?? null, $options['scope'] ?? null);
        if (null !== $value) {
            $entity->removeValue($value);
        }
    }
}
