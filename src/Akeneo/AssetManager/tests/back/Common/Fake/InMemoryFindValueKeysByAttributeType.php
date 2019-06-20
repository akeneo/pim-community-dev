<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Attribute\FindValueKeysByAttributeTypeInterface;

class InMemoryFindValueKeysByAttributeType implements FindValueKeysByAttributeTypeInterface
{
    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    public function __construct(InMemoryAttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function find(ReferenceEntityIdentifier $referenceEntityIdentifier, array $attributeTypes): array
    {
        $attributes = $this->attributeRepository->findByReferenceEntity($referenceEntityIdentifier);
        $valueKeys = [];

        /** @var AbstractAttribute $attribute */
        foreach ($attributes as $attribute) {
            if (in_array($attribute->normalize()['type'], $attributeTypes)) {
                $valueKeys[] = (string) $attribute->getIdentifier();
            }
        }

        return $valueKeys;
    }
}
