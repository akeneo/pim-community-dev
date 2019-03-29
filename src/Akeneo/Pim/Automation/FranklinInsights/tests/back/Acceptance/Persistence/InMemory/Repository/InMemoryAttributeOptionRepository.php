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

namespace Akeneo\Test\Pim\Automation\FranklinInsights\Acceptance\Persistence\InMemory\Repository;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Model\Read\AttributeOption;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeOption\Repository\AttributeOptionRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Test\Acceptance\Common\NotImplementedException;

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 */
final class InMemoryAttributeOptionRepository implements AttributeOptionRepositoryInterface
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

    public function findOneByIdentifier(AttributeCode $attributeCode, string $attributeOptionCode): ?AttributeOption
    {
        throw new NotImplementedException('findOneByIdentifier');
    }

    public function findByCodes(array $codes): array
    {
        return $this->inMemoryAttributeOptionRepository->findBy(['code' => $codes]);
    }
}
