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

    public function fromAttributeCodeAndOptionCodes(array $keys): array
    {
        $attributeOptions = $this->attributeOptionRepository->findAll();

        $results = [];
        /** @var AttributeOption $attributeOption */
        foreach ($attributeOptions as $attributeOption) {
            if (!$this->findInKeys($keys, $attributeOption->getAttribute()->getCode(), $attributeOption->getCode())) {
                continue;
            }

            $key = sprintf('%s.%s', $attributeOption->getAttribute()->getCode(), $attributeOption->getCode());
            $results[$key] = [];

            /** @var AttributeOptionValue $value */
            foreach ($attributeOption->getOptionValues() as $value) {
                $results[$key][$value->getLocale()] = $value->getValue();
            }
        }

        return $results;
    }

    private function findInKeys(array $keys, string $attributeCode, string $optionCode): bool
    {
        foreach ($keys as $key) {
            $chunks = explode('.', $key);
            if ($attributeCode === $chunks[0] && $optionCode === $chunks[1]) {
                return true;
            }
        }

        return false;
    }
}
