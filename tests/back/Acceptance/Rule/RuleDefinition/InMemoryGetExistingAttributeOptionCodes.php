<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace AkeneoEnterprise\Test\Acceptance\Rule\RuleDefinition;

use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionCodes;
use Akeneo\Pim\Structure\Component\Repository\AttributeOptionRepositoryInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class InMemoryGetExistingAttributeOptionCodes implements GetExistingAttributeOptionCodes
{
    /** @var AttributeOptionRepositoryInterface */
    private $attributeOptionRepository;

    public function __construct(AttributeOptionRepositoryInterface $attributeOptionRepository)
    {
        $this->attributeOptionRepository = $attributeOptionRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function fromOptionCodesByAttributeCode(array $optionCodesIndexedByAttributeCodes): array
    {
        $results = [];

        /** @var AttributeOption $option */
        foreach ($this->attributeOptionRepository->findAll() as $option) {
            foreach ($optionCodesIndexedByAttributeCodes as $attributeCode => $optionCodes) {
                foreach ($optionCodes as $optionCode) {
                    if ($optionCode === $option->getCode() && $attributeCode === $option->getAttribute()->getCode()) {
                        $results[$option->getAttribute()->getCode()][] = $option->getCode();
                        break;
                    }
                }
            }
        }

        return $results;
    }
}
