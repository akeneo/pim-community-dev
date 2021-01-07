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

namespace Akeneo\AssetManager\Common\Fake;

use Akeneo\AssetManager\Domain\Event\AttributeDeletedEvent;
use Akeneo\AssetManager\Domain\Event\BeforeAttributeDeletedEvent;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
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

        $attributesForEntity = $this->findByAssetFamily($attribute->getAssetFamilyIdentifier());
        foreach ($attributesForEntity as $attributeForEntity) {
            if ($attribute->getOrder()->equals($attributeForEntity->getOrder())) {
                throw new \Exception('An attribute already has this order for this asset family');
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

    public function getByCodeAndAssetFamilyIdentifier(AttributeCode $code, AssetFamilyIdentifier $assetFamilyIdentifier): AbstractAttribute
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute->getAssetFamilyIdentifier()->equals($assetFamilyIdentifier) &&
                $attribute->getCode()->equals($code)) {
                return $attribute;
            }
        }

        throw AttributeNotFoundException::withAssetFamilyAndAttributeCode($assetFamilyIdentifier, $code);
    }

    /**
     * {@inheritdoc}
     */
    public function findByAssetFamily(AssetFamilyIdentifier $assetFamilyIdentifier): array
    {
        $attributes = [];
        foreach ($this->attributes as $attribute) {
            if ($attribute->getAssetFamilyIdentifier()->equals($assetFamilyIdentifier)) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * {@inheritdoc}
     */
    public function countByAssetFamily(AssetFamilyIdentifier $assetFamilyIdentifier): int
    {
        return count($this->findByAssetFamily($assetFamilyIdentifier));
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
                $this->getAssetFamilyIdentifier($attributeIdentifier),
                $attributeIdentifier
            ),
            BeforeAttributeDeletedEvent::class
        );

        unset($this->attributes[(string) $attributeIdentifier]);

        $this->eventDispatcher->dispatch(
            new AttributeDeletedEvent($attribute->getAssetFamilyIdentifier(), $attributeIdentifier),
            AttributeDeletedEvent::class
        );
    }

    public function nextIdentifier(
        AssetFamilyIdentifier $assetFamilyIdentifier,
        AttributeCode $attributeCode
    ): AttributeIdentifier {
        return AttributeIdentifier::create(
            (string) $assetFamilyIdentifier,
            (string) $attributeCode,
            md5(sprintf('%s_%s', $assetFamilyIdentifier, $attributeCode))
        );
    }

    /**
     * Find a asset by its code and entity.
     * It's a tooling method not present in the main interface, because we need a way to retrieve attributes by their
     * code only in acceptance test. The real application will always use identifiers.
     *
     * @param AssetFamilyIdentifier $assetFamilyIdentifier
     * @param AttributeCode            $code
     *
     * @return AbstractAttribute
     */
    public function getByAssetFamilyAndCode(string $entityCode, string $attributeCode): AbstractAttribute
    {
        $entityIdentifier = AssetFamilyIdentifier::fromString($entityCode);
        $code = AttributeCode::fromString($attributeCode);

        foreach ($this->attributes as $attribute) {
            if ($attribute->getCode()->equals($code) && $attribute->getAssetFamilyIdentifier()->equals($entityIdentifier)) {
                return $attribute;
            }
        }
        throw new \InvalidArgumentException(
            sprintf('Could not find attribute with "%s" for entity "%s"', $attributeCode, $entityCode)
        );
    }

    private function getAssetFamilyIdentifier(AttributeIdentifier $attributeIdentifier): AssetFamilyIdentifier
    {
        foreach ($this->attributes as $attribute) {
            if ($attribute->getIdentifier()->equals($attributeIdentifier)) {
                return $attribute->getAssetFamilyIdentifier();
            }
        }

        throw new \Exception(
            sprintf('Asset family identifier not found for attribute %s', (string) $attributeIdentifier)
        );
    }
}
