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
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\EnsureFranklinAttributeGroupExistsInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeGroup;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Model\Write\Attribute;
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

    private $ensureFranklinAttributeGroupExists;

    public function __construct(
        AttributeFactory $attributeFactory,
        AttributeUpdater $attributeUpdater,
        InMemoryAttributeRepository $attributeRepository,
        InMemoryAttributeGroupRepository $attributeGroupRepository,
        EnsureFranklinAttributeGroupExistsInterface $ensureFranklinAttributeGroupExists
    ) {
        $this->attributeFactory = $attributeFactory;
        $this->attributeUpdater = $attributeUpdater;
        $this->attributeRepository = $attributeRepository;
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->ensureFranklinAttributeGroupExists = $ensureFranklinAttributeGroupExists;
    }

    public function create(Attribute $attributeToCreate): void
    {
        $this->ensureFranklinAttributeGroupExists->ensureExistence();

        $attribute = $this->attributeFactory->create();
        $attribute->setCode((string) $attributeToCreate->getCode());
        $attribute->setType((string) $attributeToCreate->getType());
        $this->attributeUpdater->update($attribute, ['group' => FranklinAttributeGroup::CODE]);

        $this->attributeRepository->save($attribute);
    }

    public function bulkCreate(array $attributesToCreate): array
    {
        $this->ensureFranklinAttributeGroupExists->ensureExistence();

        foreach ($attributesToCreate as $attributeToCreate) {
            $attribute = $this->attributeFactory->create();
            $attribute->setCode((string) $attributeToCreate->getCode());
            $attribute->setType((string) $attributeToCreate->getType());
            $this->attributeUpdater->update($attribute, ['group' => FranklinAttributeGroup::CODE]);
            $this->attributeRepository->save($attribute);
        }

        return $attributesToCreate;
    }
}
