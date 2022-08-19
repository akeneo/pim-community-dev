<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence;

use Akeneo\Catalogs\Application\Persistence\FindOneAttributeByCodeQueryInterface;
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

    /**
     * @return array{code: string, label: string, type: string, scopable: bool, localizable: bool}
     */
    public function execute(string $code): ?array
    {
        /** @var AttributeInterface|null $attribute */
        $attribute = $this->repository->findOneByIdentifier($code);

        if (null === $attribute) {
            return null;
        }

        return [
            'code' => $attribute->getCode(),
            'label' => $attribute->getLabel(),
            'type' => $attribute->getType(),
            'scopable' => $attribute->isScopable(),
            'localizable' => $attribute->isLocalizable(),
            'measurement_family' => $attribute->getMetricFamily(),
            'default_measurement_unit' => $attribute->getDefaultMetricUnit(),
        ];
    }
}
