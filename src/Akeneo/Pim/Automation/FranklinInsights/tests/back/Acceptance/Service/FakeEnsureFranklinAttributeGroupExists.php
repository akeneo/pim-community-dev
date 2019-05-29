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

use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\EnsureFranklinAttributeGroupExistsInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeGroup;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Test\Acceptance\AttributeGroup\InMemoryAttributeGroupRepository;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class FakeEnsureFranklinAttributeGroupExists implements EnsureFranklinAttributeGroupExistsInterface
{
    private $factory;

    private $repository;

    public function __construct(
        SimpleFactoryInterface $factory,
        InMemoryAttributeGroupRepository $repository
    ) {
        $this->factory = $factory;
        $this->repository = $repository;
    }

    public function ensureExistence(): void
    {
        $attributeGroup = $this->repository->findOneByIdentifier(FranklinAttributeGroup::CODE);
        if ($attributeGroup instanceof AttributeGroupInterface) {
            return;
        }

        $attributeGroup = $this->factory->create();
        $attributeGroup->setCode(FranklinAttributeGroup::CODE);
        $attributeGroup->setLocale('en_US');
        $attributeGroup->setLabel(FranklinAttributeGroup::LABEL);

        $this->repository->save($attributeGroup);
    }
}
