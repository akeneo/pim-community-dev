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
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\FindOrCreateFranklinAttributeGroupInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeGroup;
use Akeneo\Pim\Structure\Component\Factory\AttributeFactory;
use Akeneo\Pim\Structure\Component\Updater\AttributeUpdater;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;
use Akeneo\Test\Acceptance\AttributeGroup\InMemoryAttributeGroupRepository;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class FakeCreateAttribute implements CreateAttributeInterface
{
    /** @var AttributeFactory */
    private $attributeFactory;

    /** @var AttributeUpdater */
    private $attributeUpdater;

    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    /** @var InMemoryAttributeGroupRepository */
    private $attributeGroupRepository;

    private $findOrCreateFranklinAttributeGroup;

    public function __construct(
        AttributeFactory $attributeFactory,
        AttributeUpdater $attributeUpdater,
        InMemoryAttributeRepository $attributeRepository,
        InMemoryAttributeGroupRepository $attributeGroupRepository,
        FindOrCreateFranklinAttributeGroupInterface $findOrCreateFranklinAttributeGroup
    ) {
        $this->attributeFactory = $attributeFactory;
        $this->attributeUpdater = $attributeUpdater;
        $this->attributeRepository = $attributeRepository;
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->findOrCreateFranklinAttributeGroup = $findOrCreateFranklinAttributeGroup;
    }

    public function create(
        AttributeCode $attributeCode,
        AttributeLabel $attributeLabel,
        AttributeType $attributeType
    ): void {
        $attributeGroupCode = $this->findOrCreateFranklinAttributeGroup->findOrCreate();

        $attribute = $this->attributeFactory->create();
        $attribute->setCode((string) $attributeCode);
        $attribute->setType((string) $attributeType);
        $this->attributeUpdater->update($attribute, ['group' => (string) $attributeGroupCode]);

        $this->attributeRepository->save($attribute);
    }
}
