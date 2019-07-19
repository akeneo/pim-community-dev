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

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Acceptance\Persistence\InMemory\Query;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOptionMapping\Model\Read\AttributeOption;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOptionMapping\Repository\AttributeOptionRepositoryInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeOption\GetExistingAttributeOptionCodes;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
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

    public function fromOptionCodesByAttributeCode(array $optionCodesIndexedByAttributeCodes): array
    {
        $results = [];

        /** @var AttributeOption $option */
        foreach ($this->attributeOptionRepository->findByCodes(array_merge(...array_values($optionCodesIndexedByAttributeCodes))) as $option) {
            $results[(string) $option->getAttributeCode()][] = $option->getCode();
        }

        return $results;
    }
}
