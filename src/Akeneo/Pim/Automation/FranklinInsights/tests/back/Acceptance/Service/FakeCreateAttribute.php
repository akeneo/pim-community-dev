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

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Acceptance\Service;

use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\CreateAttributeInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeLabel;
use Akeneo\Pim\Structure\Component\Factory\AttributeFactory;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;
use Akeneo\Test\Acceptance\AttributeGroup\InMemoryAttributeGroupRepository;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class FakeCreateAttribute implements CreateAttributeInterface
{
    /** @var AttributeFactory */
    private $attributeFactory;

    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    /** @var InMemoryAttributeGroupRepository */
    private $attributeGroupRepository;

    public function __construct(
        AttributeFactory $attributeFactory,
        InMemoryAttributeRepository $attributeRepository,
        InMemoryAttributeGroupRepository $attributeGroupRepository
    ) {
        $this->attributeFactory = $attributeFactory;
        $this->attributeRepository = $attributeRepository;
        $this->attributeGroupRepository = $attributeGroupRepository;
    }

    public function create(AttributeCode $attributeCode, AttributeLabel $attributeLabel, string $attributeType, string $attributeGroupCode): void
    {
        $attributeGroup = $this->attributeGroupRepository->findOneByIdentifier($attributeGroupCode);

        $attribute = $this->attributeFactory->create();
        $attribute->setCode((string) $attributeCode);
        $attribute->setType($attributeType);
        $attribute->setGroup($attributeGroup);

        $this->attributeRepository->save($attribute);
    }
}
