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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Query\InMemory;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Query\SelectAttributeOptionCodesByIdentifiersQueryInterface;

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 */
class InMemorySelectAttributeOptionCodesByIdentifiersQuery implements SelectAttributeOptionCodesByIdentifiersQueryInterface
{
    /**
     * @var \Akeneo\Test\Acceptance\AttributeOption\InMemoryAttributeOptionRepository
     */
    private $inMemoryAttributeOptionRepository;

    public function __construct(
        \Akeneo\Test\Acceptance\AttributeOption\InMemoryAttributeOptionRepository $inMemoryAttributeOptionRepository
    ) {
        $this->inMemoryAttributeOptionRepository = $inMemoryAttributeOptionRepository;
    }

    /**
     * Return an array of attribute option codes.
     *
     * @param string[] $attributeOptionCodes
     *
     * @return string[]
     */
    public function execute(string $attributeCode, array $attributeOptionCodes): array
    {
        $rows = $this->inMemoryAttributeOptionRepository->findCodesByIdentifiers($attributeCode, $attributeOptionCodes);

        return array_map(
            function ($row) {
                return $row['code'];
            },
            $rows
        );
    }
}
