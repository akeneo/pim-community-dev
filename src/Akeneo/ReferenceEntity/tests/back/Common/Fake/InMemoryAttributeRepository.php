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

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Event\AttributeDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Event\BeforeAttributeDeletedEvent;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeNotFoundException;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class InMemoryAttributeRepository implements AttributeRepositoryInterface
{
    /** @var AbstractAttribute[] */
    private $attributes = [];

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function create(AbstractAttribute $attribute): void
    {
        if (isset($this->attributes[(string) $attribute->getIdentifier()])) {
            throw new \RuntimeException('Attribute already exists');
        }

        $attributesForEntity = $this->findByReferenceEntity($attribute->getReferenceEntityIdentifier());
        foreach ($attributesForEntity as $attributeForEntity) {
            if ($attribute->getOrder()->equals($attributeForEntity->getOrder())) {
                throw new \Exception('An attribute already has this order for this reference entity');
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
    public function findByReferenceEntity(ReferenceEntityIdentifier $referenceEntityIdentifier): array
    {
        $attributes = [];
        foreach ($this->attributes as $attribute) {
            if ($attribute->getReferenceEntityIdentifier()->equals($referenceEntityIdentifier)) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function countByReferenceEntity(ReferenceEntityIdentifier $referenceEntityIdentifier): int
    {
        return count($this->findByReferenceEntity($referenceEntityIdentifier));
    }

    /**
     * @return AbstractAttribute[]
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function deleteByIdentifier(AttributeIdentifier $attributeIdentifier): void
    {
        $attribute = $this->attributes[(string) $attributeIdentifier] ?? null;
        if (null === $attribute) {
            throw AttributeNotFoundException::withIdentifier($attributeIdentifier);
        }

        $this->eventDispatcher->dispatch(
            new BeforeAttributeDeletedEvent(
                $this->getReferenceEntityIdentifier($attributeIdentifier),
                $attributeIdentifier
            ),
            BeforeAttributeDeletedEvent::class
        );

        unset($this->attributes[(string) $attributeIdentifier]);

        $this->eventDispatcher->dispatch(
            new AttributeDeletedEvent($attribute->getReferenceEntityIdentifier(), $attributeIdentifier),
            AttributeDeletedEvent::class
        );
    }

    public function nextIdentifier(
        ReferenceEntityIdentifier $referenceEntityIdentifier,
        AttributeCode $attributeCode
    ): AttributeIdentifier {
        return AttributeIdentifier::create(
            (string) $referenceEntityIdentifier,
            (string) $attributeCode,
            md5(sprintf('%s_%s', $referenceEntityIdentifier, $attributeCode))
        );
    }

    /**
     * Find a record by its code and entity.
     * It's a tooling method not present in the main interface, because we need a way to retrieve attributes by their
     * code only in acceptance test. The real application will always use identifiers.
     *
     * @param ReferenceEntityIdentifier $referenceEntityIdentifier
     * @param AttributeCode            $code
     *
     * @return AbstractAttribute
     */
    public function getByReferenceEntityAndCode(string $entityCode, string $attributeCode): AbstractAttribute
    {
        $entityIdentifier = ReferenceEntityIdentifier::fromString($entityCode);
        $code = AttributeCode::fromString($attributeCode);

        foreach ($this->attributes as $attribute) {
            if ($attribute->getCode()->equals($code) && $attribute->getReferenceEntityIdentifier()->equals($entityIdentifier)) {
                return $attribute;
            }
        }
        throw new \InvalidArgumentException(
            sprintf('Could not find attribute with "%s" for entity "%s"', $attributeCode, $entityCode)
        );
    }

    private function getReferenceEntityIdentifier(AttributeIdentifier $attributeIdentifier): ReferenceEntityIdentifier
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute->getIdentifier()->equals($attributeIdentifier)) {
                return $attribute->getReferenceEntityIdentifier();
            }
        }

        throw new \Exception(
            sprintf('Reference entity identifier not found for attribute %s', (string) $attributeIdentifier)
        );
    }
}
