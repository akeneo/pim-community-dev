<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Attribute;

use Akeneo\Catalogs\Application\Persistence\Attribute\FindOneAttributeByCodeQueryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FindOneAttributeByCodeQuery implements FindOneAttributeByCodeQueryInterface
{
    public function __construct(private AttributeRepositoryInterface $repository)
    {
    }

    public function execute(string $code): ?array
    {
        /** @var AttributeInterface|null $attribute */
        $attribute = $this->repository->findOneByIdentifier($code);

        if (null === $attribute) {
            return null;
        }

        $normalizedAttribute = [
            'code' => $attribute->getCode(),
            'label' => $attribute->getLabel(),
            'type' => $attribute->getType(),
            'scopable' => $attribute->isScopable(),
            'localizable' => $attribute->isLocalizable(),
            'attribute_group_code' => $attribute->getGroup()->getCode(),
            'attribute_group_label' => $attribute->getGroup()->getLabel(),
        ];

        if ('pim_catalog_metric' === $attribute->getType()) {
            $normalizedAttribute['measurement_family'] = $attribute->getMetricFamily();
            $normalizedAttribute['default_measurement_unit'] = $attribute->getDefaultMetricUnit();
        }

        if ('pim_catalog_asset_collection' === $attribute->getType()) {
            $normalizedAttribute['asset_family'] = (string) $attribute->getProperty('reference_data_name');
        }

        return $normalizedAttribute;
    }
}
