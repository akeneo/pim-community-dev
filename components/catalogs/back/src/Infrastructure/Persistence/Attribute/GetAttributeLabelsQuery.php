<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Attribute;

use Akeneo\Catalogs\Application\Persistence\Attribute\GetAttributeLabelsQueryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetAttributeLabelsQuery implements GetAttributeLabelsQueryInterface
{
    public function __construct(
        private SearchableRepositoryInterface $searchableAttributeRepository,
    ) {
    }

    public function execute(array $attributeCodes): array
    {
        $attributes = $this->searchableAttributeRepository->findBySearch(options: ['identifiers' => $attributeCodes, 'locale' => null]);

        $attributeLabels = \array_reduce(
            $attributes,
            /**
             * @param array<array-key, array{code: string, label: string}> $normalized
             * @return array<array-key, array{code: string, label: string}>
             */
            static function (array $normalized, AttributeInterface $attribute): array {
                $normalized[$attribute->getCode()] = [
                    'code' => $attribute->getCode(),
                    'label' => $attribute->getLabel(),
                ];

                return $normalized;
            },
            []
        );

        return $attributeLabels;
    }
}
