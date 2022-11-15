<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Attribute;

use Akeneo\Catalogs\Application\Persistence\Attribute\SearchAttributesQueryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SearchAttributesQuery implements SearchAttributesQueryInterface
{
    private const ALLOWED_TYPES = [
        'pim_catalog_identifier',
        'pim_catalog_text',
        'pim_catalog_textarea',
        'pim_catalog_simpleselect',
        'pim_catalog_multiselect',
        'pim_catalog_number',
        'pim_catalog_metric',
        'pim_catalog_boolean',
        'pim_catalog_date',
    ];

    public function __construct(
        private SearchableRepositoryInterface $searchableAttributeRepository,
    ) {
    }

    /**
     * @return array<array{code: string, label: string, type: string, scopable: bool, localizable: bool, measurement_family?: string, default_measurement_unit?: string}>
     */
    public function execute(?string $search = null, int $page = 1, int $limit = 20, array $types = null): array
    {
        //$allowedTypes = array_filter(self::ALLOWED_TYPES, fn($type) => $types === null || in_array(str_replace('pim_catalog_','', $type), $types));

//        $allowedTypes = null === $types ? self::ALLOWED_TYPES : array_map(fn($type) =>
//            'pim_catalog_'.$type,
//            array_filter($types, fn($type) => in_array('pim_catalog_'.$type, self::ALLOWED_TYPES)));

        $allowedTypes = self::ALLOWED_TYPES;
        if($types !== null ) {
            $allowedTypes = [];
            foreach ($types as $type) {
                $type = \sprintf('pim_catalog_%s', $type);
                if (in_array($type, self::ALLOWED_TYPES)) {
                    $allowedTypes[] = $type;
                }
            }
        }

        $attributes = $this->searchableAttributeRepository->findBySearch(
            $search,
            [
                'limit' => $limit,
                'page' => $page,
                'types' => $allowedTypes,
            ],
        );

        return \array_map(
            static function (AttributeInterface $attribute): array {
                $normalizedAttribute = [
                    'code' => $attribute->getCode(),
                    'label' => $attribute->getLabel(),
                    'type' => $attribute->getType(),
                    'scopable' => $attribute->isScopable(),
                    'localizable' => $attribute->isLocalizable(),
                ];

                if ('pim_catalog_metric' === $attribute->getType()) {
                    $normalizedAttribute['measurement_family'] = $attribute->getMetricFamily();
                    $normalizedAttribute['default_measurement_unit'] = $attribute->getDefaultMetricUnit();
                }

                return $normalizedAttribute;
            },
            $attributes
        );
    }
}
