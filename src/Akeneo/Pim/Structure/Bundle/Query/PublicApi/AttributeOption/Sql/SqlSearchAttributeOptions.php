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
    public function search(string $attributeCode, SearchAttributeOptionsParameters $searchParameters): array
    {
        $options = [
            'identifier' => $attributeCode,
            'identifiers' => $searchParameters->getIncludeCodes(),
            'excludeCodes' => $searchParameters->getExcludeCodes(),
            'locale' => $searchParameters->getLocale(),
            'catalogLocale' => $searchParameters->getLocale(),
            'limit' => $searchParameters->getLimit(),
            'page' => $searchParameters->getPage(),
        ];

        $attributeOptions = $this->attributeOptionSearchableRepository->findBySearch(
            $searchParameters->getSearch(),
            $options
        );

        return array_map(
            static function ($attributeOption) {
                $labels = [];

                foreach ($attributeOption->getOptionValues() as $optionValue) {
                    $labels[$optionValue->getLocale()] = $optionValue->getValue();
                }

                return new AttributeOption($attributeOption->getCode(), $labels);
            },
            $attributeOptions,
        );
    }
}
