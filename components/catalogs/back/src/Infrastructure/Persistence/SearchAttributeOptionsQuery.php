<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\SearchAttributeOptionsQueryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SearchAttributeOptionsQuery implements SearchAttributeOptionsQueryInterface
{
    public function __construct(
        private SearchableRepositoryInterface $searchableAttributeOptionsRepository,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function execute(
        string $attribute,
        string $locale = 'en_US',
        ?string $search = null,
        int $page = 1,
        int $limit = 20
    ): array {
        $options = $this->searchableAttributeOptionsRepository->findBySearch(
            $search,
            [
                'identifier' => $attribute,
                'limit' => $limit,
                'page' => $page,
            ],
        );

        return \array_map(
            static fn (AttributeOptionInterface $option) => [
                'code' => $option->getCode(),
                'label' => $option->setLocale($locale)->getOptionValue()?->getLabel() ?: '[' . $option->getCode() . ']',
            ],
            $options
        );
    }
}
