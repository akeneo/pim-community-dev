<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Infrastructure\Query;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\FindFlattenAttributesInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Attribute\FlattenAttribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\GetViewableAttributeCodesForUserInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\Attribute\Attribute;
use Akeneo\Platform\TailoredExport\Domain\Query\Attribute\FindViewableAttributesInterface;
use Akeneo\Platform\TailoredExport\Domain\Query\Attribute\ViewableAttributesResult;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class FindViewableAttributes implements FindViewableAttributesInterface
{
    private TokenStorageInterface $tokenStorage;
    private FindFlattenAttributesInterface $findFlattenAttributes;
    private GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser;

    public function __construct(
        FindFlattenAttributesInterface $findFlattenAttributes,
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodesForUser,
        TokenStorageInterface $tokenStorage
    ) {
        $this->findFlattenAttributes = $findFlattenAttributes;
        $this->getViewableAttributeCodesForUser = $getViewableAttributeCodesForUser;
        $this->tokenStorage = $tokenStorage;
    }

    public function execute(
        string $localeCode,
        int $limit,
        array $attributeTypes = null,
        int $offset = 0,
        string $search = null
    ): ViewableAttributesResult {
        $viewableAttributes = [];
        $currentOffset = max($offset, 0);

        do {
            $attributes = $this->findAttributes($localeCode, $limit, $attributeTypes, $currentOffset, $search);

            if (empty($attributes)) {
                return new ViewableAttributesResult($currentOffset, $viewableAttributes);
            }

            $viewableAttributes = array_merge($viewableAttributes, $this->filterViewableAttributes($attributes));
            $currentOffset += count($attributes);
        } while (count($viewableAttributes) < $limit);

        $currentOffset -= count($viewableAttributes) - $limit;

        return new ViewableAttributesResult($currentOffset, array_slice($viewableAttributes, 0, $limit));
    }

    private function findAttributes(
        string $localeCode,
        int $limit,
        ?array $attributeTypes,
        $currentOffset,
        ?string $search
    ): array {
        $flattenAttributes = $this->findFlattenAttributes->execute(
            $localeCode,
            $limit,
            $attributeTypes,
            $currentOffset,
            $search
        );

        return array_map(
            static fn (FlattenAttribute $flattenAttribute) => new Attribute(
                $flattenAttribute->getCode(),
                $flattenAttribute->getLabel(),
                $flattenAttribute->getAttributeGroupCode(),
                $flattenAttribute->getAttributeGroupLabel()
            ),
            $flattenAttributes
        );
    }

    /**
     * @var Attribute[] $attributes
     *
     * @return Attribute[]
     */
    private function filterViewableAttributes(array $attributes): array
    {
        $userId = $this->getUserId();
        $attributeCodes = array_map(static fn (Attribute $attribute) => $attribute->getCode(), $attributes);
        $viewableAttributeCodes = $this->getViewableAttributeCodesForUser->forAttributeCodes($attributeCodes, $userId);

        return array_filter(
            $attributes,
            static fn ($attribute) => in_array($attribute->getCode(), $viewableAttributeCodes)
        );
    }

    private function getUserId(): int
    {
        $user = $this->tokenStorage->getToken()->getUser();

        if (!$user instanceof UserInterface) {
            throw new \RuntimeException('User is not authenticated');
        }

        return $user->getId();
    }
}
