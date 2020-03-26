<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Webmozart\Assert\Assert;

/**
 * Clear the attribute value of an entity.
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributeClearer implements ClearerInterface
{
    /** @var GetAttributes */
    private $getAttributes;

    public function __construct(GetAttributes $getAttributes)
    {
        $this->getAttributes = $getAttributes;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsProperty(string $property): bool
    {
        return null !== $this->getAttributes->forCode($property);
    }

    /**
     * {@inheritDoc}
     */
    public function clear($entity, string $attributeCode, array $options = []): void
    {
        Assert::isInstanceOf($entity, EntityWithValuesInterface::class);
        Assert::true(
            $this->supportsProperty($attributeCode),
            sprintf('The clearer does not handle the "%s" property.', $attributeCode)
        );

        $value = $entity->getValue($attributeCode, $options['locale'] ?? null, $options['scope'] ?? null);
        if (null !== $value) {
            $entity->removeValue($value);
        }
    }
}
