<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Attribute;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryGetAttributes implements GetAttributes
{
    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    public function __construct(InMemoryAttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function forCodes(array $attributeCodes): array
    {
        $attributes = [];
        foreach ($attributeCodes as $attributeCode) {
            /** @var $attribute AttributeInterface*/
            $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
            if (null !== $attribute) {
                $attributes[$attributeCode] = new Attribute(
                    $attributeCode,
                    $attribute->getType(),
                    $attribute->getProperties(),
                    (bool) $attribute->isLocalizable(),
                    (bool) $attribute->isScopable(),
                    $attribute->getMetricFamily(),
                    $attribute->isDecimalsAllowed(),
                    $attribute->getBackendType(),
                    $attribute->getAvailableLocaleCodes()
                );
            }
        }

        return $attributes;
    }

    public function forCode(string $attributeCode): ?Attribute
    {
        return $this->forCodes([$attributeCode])[$attributeCode] ?? null;
    }
}
