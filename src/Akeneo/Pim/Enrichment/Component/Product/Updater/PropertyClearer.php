<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerRegistryInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyClearerInterface;

/**
 * Clears a property of an entity. Could be an attribute or a field of a product for example.
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class PropertyClearer implements PropertyClearerInterface
{
    /** @var ClearerRegistryInterface */
    private $clearerRegistry;

    public function __construct(ClearerRegistryInterface $clearerRegistry)
    {
        $this->clearerRegistry = $clearerRegistry;
    }

    /**
     * {@inheritDoc}
     */
    public function clear($entity, string $property, array $options = []): void
    {
        $clearer = $this->clearerRegistry->getClearer($property);
        if (null === $clearer) {
            throw UnknownPropertyException::unknownProperty($property);
        }

        $clearer->clear($entity, $property, $options);
    }
}
