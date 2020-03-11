<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\AttributeOption;

use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionValue;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionsWithValues;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class InMemoryGetExistingAttributeOptionsWithValues implements GetExistingAttributeOptionsWithValues
{
    /** @var InMemoryAttributeOptionRepository */
    private $attributeOptionRepository;

    public function __construct(InMemoryAttributeOptionRepository $attributeOptionRepository)
    {
        $this->attributeOptionRepository = $attributeOptionRepository;
    }

    public function fromAttributeCodeAndOptionCodes(string $attributeCode, array $optionCodes): array
    {
        $attributeOptions = $this->attributeOptionRepository->findAll();

        $results = [];
        /** @var AttributeOption $attributeOption */
        foreach ($attributeOptions as $attributeOption) {
            if ($attributeOption->getAttribute()->getCode() !== $attributeCode
                || !in_array($attributeOption->getCode(), $optionCodes)
            ) {
                continue;
            }

            $results[$attributeOption->getCode()] = [];

            /** @var AttributeOptionValue $value */
            foreach ($attributeOption->getOptionValues() as $value) {
                $results[$attributeOption->getCode()][$value->getLocale()] = $value->getValue();
            }
        }

        return $results;
    }
}
