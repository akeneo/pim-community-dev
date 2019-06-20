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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\AddAttributeToFamilyInterface;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\CreateAttributeInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Write\AttributeMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeAddedToFamily;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeCreated;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository\FranklinAttributeAddedToFamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository\FranklinAttributeCreatedRepositoryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class CreateAttributeInFamilyHandler
{
    private $createAttribute;

    private $updateFamily;

    private $franklinAttributeCreatedRepository;

    private $franklinAttributeAddedToFamilyRepository;

    public function __construct(
        CreateAttributeInterface $createAttribute,
        AddAttributeToFamilyInterface $updateFamily,
        FranklinAttributeCreatedRepositoryInterface $franklinAttributeCreatedRepository,
        FranklinAttributeAddedToFamilyRepositoryInterface $franklinAttributeAddedToFamilyRepository
    ) {
        $this->createAttribute = $createAttribute;
        $this->updateFamily = $updateFamily;
        $this->franklinAttributeCreatedRepository = $franklinAttributeCreatedRepository;
        $this->franklinAttributeAddedToFamilyRepository = $franklinAttributeAddedToFamilyRepository;
    }

    public function handle(CreateAttributeInFamilyCommand $command): void
    {
        $pimAttributeType = $this->convertFranklinAttributeTypeToPimAttributeType($command->getFranklinAttributeType());

        $this->createAttribute->create(
            $command->getPimAttributeCode(),
            new AttributeLabel((string) $command->getFranklinAttributeLabel()),
             $pimAttributeType
        );
        $this->franklinAttributeCreatedRepository->save(
            new FranklinAttributeCreated($command->getPimAttributeCode(), $pimAttributeType)
        );

        $this->updateFamily->addAttributeToFamily($command->getPimAttributeCode(), $command->getPimFamilyCode());
        $this->franklinAttributeAddedToFamilyRepository->save(
            new FranklinAttributeAddedToFamily($command->getPimAttributeCode(), $command->getPimFamilyCode())
        );
    }

    public function convertFranklinAttributeTypeToPimAttributeType(FranklinAttributeType $franklinAttributeType): AttributeType
    {
        if (FranklinAttributeType::METRIC_TYPE === (string) $franklinAttributeType) {
            return new AttributeType(AttributeTypes::TEXT);
        }

        return new AttributeType(
            array_search((string) $franklinAttributeType, AttributeMapping::AUTHORIZED_ATTRIBUTE_TYPE_MAPPINGS)
        );
    }
}
