<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ClearerInterface
{
    /**
     * Returns true if the clearer supports the given property.
     *
     * @param string $property
     * @return bool
     */
    public function supportsProperty(string $property): bool;

    /**
     * Clears the property value of the entity.
     *
     * @param mixed $entity
     * @param string $property
     * @param array $options
     */
    public function clear($entity, string $property, array $options = []): void;
}
