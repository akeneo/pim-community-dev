<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\AttributeOption;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionCodes;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryGetExistingAttributeOptionCodes implements GetExistingAttributeOptionCodes
{
    /** @var InMemoryAttributeOptionRepository */
    private $attributeOptionRepository;

    public function __construct(InMemoryAttributeOptionRepository $attributeOptionRepository)
    {
        $this->attributeOptionRepository = $attributeOptionRepository;
    }

    public function fromOptionCodesByAttributeCode(array $optionCodesIndexedByAttributeCodes): array
    {
        $existingATtributeOptionCodes = array_fill_keys(array_keys($optionCodesIndexedByAttributeCodes), []);

        foreach ($optionCodesIndexedByAttributeCodes as $attributeCode => $optionCodes) {
            foreach ($optionCodes as $optionCode) {
                if (null !== $this->attributeOptionRepository->findOneByIdentifier(sprintf('%s.%s', $attributeCode, $optionCode))) {
                    $existingATtributeOptionCodes[$attributeCode][] = $optionCode;
                }
            }
        }

        return array_filter($existingATtributeOptionCodes);
    }
}
