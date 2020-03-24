<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ClearerRegistryInterface
{
    /**
     * Register a clearer in the registry.
     *
     * @param ClearerInterface $clearer
     */
    public function register(ClearerInterface $clearer): void;

    /**
     * Get a clearer compatible with given property.
     *
     * @param string $property
     * @return ClearerInterface|null
     */
    public function getClearer(string $property): ?ClearerInterface;

    /**
     * Get an attribute clearer compatible with given attribute.
     *
     * @param Attribute $attribute
     * @return AttributeClearerInterface|null
     */
    public function getAttributeClearer(Attribute $attribute): ?AttributeClearerInterface;

    /**
     * Get a field clearer compatible with given field.
     *
     * @param string $field
     * @return FieldClearerInterface|null
     */
    public function getFieldClearer(string $field): ?FieldClearerInterface;
}
