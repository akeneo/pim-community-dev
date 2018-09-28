<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\EnrichedEntity\Common\Fake;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\EnrichedEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeNotFoundException;
use Akeneo\EnrichedEntity\Domain\Repository\AttributeRepositoryInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryAttributeRepository implements AttributeRepositoryInterface
{
    /** @var AbstractAttribute[] */
    private $attributes = [];

    public function create(AbstractAttribute $attribute): void
    {
        if (isset($this->attributes[(string) $attribute->getIdentifier()])) {
            throw new \RuntimeException('Attribute already exists');
        }

        $attributesForEntity = $this->findByEnrichedEntity($attribute->getEnrichedEntityIdentifier());
        foreach ($attributesForEntity as $attributeForEntity) {
            if ($attribute->getOrder()->equals($attributeForEntity->getOrder())) {
                throw new \Exception('An attribute already has this order for this enriched entity');
            }
        }

        $this->attributes[(string) $attribute->getIdentifier()] = $attribute;
    }

    public function update(AbstractAttribute $attribute): void
    {
        if (!isset($this->attributes[(string) $attribute->getIdentifier()])) {
            throw new \RuntimeException('Expected to update one attribute, but none was updated');
        }
        $this->attributes[(string) $attribute->getIdentifier()] = $attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function getByIdentifier(AttributeIdentifier $identifier): AbstractAttribute
    {
        $attribute = $this->attributes[(string) $identifier] ?? null;
        if (null === $attribute) {
            throw AttributeNotFoundException::withIdentifier($identifier);
        }

        return $attribute;
    }

    /**
     * {@inheritdoc}
     */
    public function findByEnrichedEntity(EnrichedEntityIdentifier $enrichedEntityIdentifier): array
    {
        $attributes = [];
        foreach ($this->attributes as $attribute) {
            if ($attribute->getEnrichedEntityIdentifier()->equals($enrichedEntityIdentifier)) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * @return AbstractAttribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function deleteByIdentifier(AttributeIdentifier $identifier): void
    {
        $attribute = $this->attributes[(string) $identifier] ?? null;
        if (null === $attribute) {
            throw AttributeNotFoundException::withIdentifier($identifier);
        }

        unset($this->attributes[(string) $identifier]);
    }

    public function nextIdentifier(
        EnrichedEntityIdentifier $enrichedEntityIdentifier,
        AttributeCode $attributeCode
    ): AttributeIdentifier {
        return AttributeIdentifier::create(
            (string) $enrichedEntityIdentifier,
            (string) $attributeCode,
            md5(sprintf('%s_%s', $enrichedEntityIdentifier, $attributeCode))
        );
    }

    /**
     * Find a record by its code and entity.
     * It's a tooling method not present in the main interface, because we need a way to retrieve attributes by their
     * code only in acceptance test. The real application will always use identifiers.
     *
     * @param EnrichedEntityIdentifier $enrichedEntityIdentifier
     * @param AttributeCode            $code
     *
     * @return AbstractAttribute
     */
    public function getByEnrichedEntityAndCode(string $entityCode, string $attributeCode): AbstractAttribute
    {
        $entityIdentifier = EnrichedEntityIdentifier::fromString($entityCode);
        $code = AttributeCode::fromString($attributeCode);

        foreach ($this->attributes as $attribute) {
            if ($attribute->getCode()->equals($code) && $attribute->getEnrichedEntityIdentifier()->equals($entityIdentifier)) {
                return $attribute;
            }
        }
        throw new \InvalidArgumentException(
            sprintf('Could not find attribute with "%s" for entity "%s"', $attributeCode, $entityCode)
        );
    }
}
