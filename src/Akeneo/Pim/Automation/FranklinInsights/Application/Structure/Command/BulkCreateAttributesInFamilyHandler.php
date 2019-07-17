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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeAddedToFamily;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeCreated;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository\FranklinAttributeAddedToFamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository\FranklinAttributeCreatedRepositoryInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class BulkCreateAttributesInFamilyHandler
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

    public function handle(BulkCreateAttributesInFamilyCommand $command): void
    {
        $attributes = [];
        $attributeCodes = [];
        $franklinAttributeCreatedEvents = [];
        $franklinAttributeAddedToFamilyEvents = [];

        foreach ($command->getAttributesToCreate() as $attributeToCreate) {
            $attributeType = $this->convertFranklinAttributeTypeToPimAttributeType($attributeToCreate['franklinAttributeType']);
            $attributeCode = AttributeCode::fromLabel((string) $attributeToCreate['franklinAttributeLabel']);

            $attributes[] = [
                'attributeCode' => $attributeCode,
                'attributeLabel' => new AttributeLabel((string) $attributeToCreate['franklinAttributeLabel']),
                'attributeType' => $attributeType,
            ];

            $attributeCodes[] = $attributeCode;
            $franklinAttributeCreatedEvents[] = new FranklinAttributeCreated($attributeCode, $attributeType);
            $franklinAttributeAddedToFamilyEvents[] = new FranklinAttributeAddedToFamily($attributeCode, $command->getPimFamilyCode());
        }

        $this->createAttribute->bulkCreate($attributes);
        $this->franklinAttributeCreatedRepository->saveAll($franklinAttributeCreatedEvents);

        $this->updateFamily->bulkAddAttributesToFamily($command->getPimFamilyCode(), $attributeCodes);
        $this->franklinAttributeAddedToFamilyRepository->saveAll($franklinAttributeAddedToFamilyEvents);
    }

    private function convertFranklinAttributeTypeToPimAttributeType(FranklinAttributeType $franklinAttributeType): AttributeType
    {
        $pimAttributeType = $franklinAttributeType->convertToPimAttributeType();
        if (FranklinAttributeType::METRIC_TYPE === (string) $franklinAttributeType) {
            return new AttributeType(AttributeTypes::TEXT);
        }

        return $pimAttributeType;
    }
}
