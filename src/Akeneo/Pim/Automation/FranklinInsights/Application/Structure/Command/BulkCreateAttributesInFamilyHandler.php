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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeAddedToFamily;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeCreated;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Model\Write\Attribute;
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

    /**
     * @param BulkCreateAttributesInFamilyCommand $command
     *
     * @return int Number of attributes successfully created.
     */
    public function handle(BulkCreateAttributesInFamilyCommand $command): int
    {
        $attributes = [];
        foreach ($command->getAttributesToCreate() as $attributeToCreate) {
            $attributeType = $this->convertFranklinAttributeTypeToPimAttributeType($attributeToCreate['franklinAttributeType']);
            $attributeCode = AttributeCode::fromLabel((string) $attributeToCreate['franklinAttributeLabel']);

            $attributes[] = new Attribute(
                $attributeCode,
                new AttributeLabel((string) $attributeToCreate['franklinAttributeLabel']),
                $attributeType
            );
        }

        $createdAttributes = $this->createAttribute->bulkCreate($attributes);

        if (empty($createdAttributes)) {
            return 0;
        }

        $createdAttributesCodes = [];
        $franklinAttributeCreatedEvents = [];
        $franklinAttributeAddedToFamilyEvents = [];

        foreach ($createdAttributes as $createdAttribute) {
            $createdAttributesCodes[] = $createdAttribute->getCode();
            $franklinAttributeCreatedEvents[] = new FranklinAttributeCreated($createdAttribute->getCode(), $createdAttribute->getType());
            $franklinAttributeAddedToFamilyEvents[] = new FranklinAttributeAddedToFamily($createdAttribute->getCode(), $command->getPimFamilyCode());
        }

        $this->franklinAttributeCreatedRepository->saveAll($franklinAttributeCreatedEvents);

        $this->updateFamily->bulkAddAttributesToFamily($command->getPimFamilyCode(), $createdAttributesCodes);
        $this->franklinAttributeAddedToFamilyRepository->saveAll($franklinAttributeAddedToFamilyEvents);

        return count($createdAttributes);
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
