<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\AttributeClearerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerRegistryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\FieldClearerInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\PropertyClearerInterface;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class PropertyClearer implements PropertyClearerInterface
{
    /** @var GetAttributes */
    private $getAttributes;

    /** @var ClearerRegistryInterface */
    private $clearerRegistry;

    public function __construct(GetAttributes $getAttributes, ClearerRegistryInterface $clearerRegistry)
    {
        $this->getAttributes = $getAttributes;
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

        if ($clearer instanceof AttributeClearerInterface) {
            $attribute = $this->getAttribute($property);
            $clearer->clear($entity, $attribute, $options);
        } elseif ($clearer instanceof FieldClearerInterface) {
            $clearer->clear($entity, $property, $options);
        } else {
            throw UnknownPropertyException::unknownProperty($property);
        }
    }

    private function getAttribute(string $attributeCode): Attribute
    {
        $attribute = $this->getAttributes->forCode($attributeCode);
        if (null === $attribute) {
            throw new ResourceNotFoundException(AttributeInterface::class);
        }

        return $attribute;
    }
}
