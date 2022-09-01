<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\SearchAttributesQueryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SearchAttributesQuery implements SearchAttributesQueryInterface
{
    private const ALLOWED_TYPES = [
        'pim_catalog_text',
        'pim_catalog_textarea',
        'pim_catalog_simpleselect',
        'pim_catalog_multiselect',
        'pim_catalog_number',
    ];

    public function __construct(
        private SearchableRepositoryInterface $searchableAttributeRepository,
    ) {
    }

    /**
     * @return array<array{code: string, label: string, type: string, scopable: bool, localizable: bool}>
     */
    public function execute(?string $search = null, int $page = 1, int $limit = 20): array
    {
        $attributes = $this->searchableAttributeRepository->findBySearch(
            $search,
            [
                'limit' => $limit,
                'page' => $page,
                'types' => self::ALLOWED_TYPES,
            ],
        );

        return \array_map(
            static fn (AttributeInterface $attribute) => [
                'code' => $attribute->getCode(),
                'label' => $attribute->getLabel(),
                'type' => $attribute->getType(),
                'scopable' => $attribute->isScopable(),
                'localizable' => $attribute->isLocalizable(),
            ],
            $attributes
        );
    }
}
