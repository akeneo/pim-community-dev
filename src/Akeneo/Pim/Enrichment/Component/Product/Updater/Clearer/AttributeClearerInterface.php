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
interface AttributeClearerInterface extends ClearerInterface
{
    public function supportsAttribute(Attribute $attribute): bool;

    /**
     * Clears the attribute value of the entity.
     */
    public function clear(EntityWithValuesInterface $entity, Attribute $attribute, array $options = []): void;
}
