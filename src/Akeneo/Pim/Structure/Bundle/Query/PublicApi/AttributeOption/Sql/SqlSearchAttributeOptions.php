<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\Query\PublicApi\AttributeOption\Sql;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\InternalApi\AttributeOptionSearchableRepository;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\AttributeOption;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\SearchAttributeOptionsInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\SearchAttributeOptionsParameters;

/**
 * @author Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlSearchAttributeOptions implements SearchAttributeOptionsInterface
{
    private AttributeOptionSearchableRepository $attributeOptionSearchableRepository;

    public function __construct(
        AttributeOptionSearchableRepository $attributeOptionSearchableRepository
    ) {
        $this->attributeOptionSearchableRepository = $attributeOptionSearchableRepository;
    }

    /**
     * @return AttributeOption[]
     */
    public function search(SearchAttributeOptionsParameters $searchParameters): array
    {
        $options = [
            'identifier' => $searchParameters->getAttributeCode(),
            'identifiers' => $searchParameters->getAttributeOptionCodes(),
            'locale' => $searchParameters->getLocale(),
            'catalogLocale' => $searchParameters->getCatalogLocale(),
            'limit' => $searchParameters->getLimit(),
            'page' => $searchParameters->getPage(),
        ];

        return array_map(function($attributeOption) {
            return new AttributeOption($attributeOption->getCode(), $attributeOption->getOptionValues());
        }, $this->attributeOptionSearchableRepository->findBySearch(
            $searchParameters->getSearch(),
            $options
        ));
    }
}
