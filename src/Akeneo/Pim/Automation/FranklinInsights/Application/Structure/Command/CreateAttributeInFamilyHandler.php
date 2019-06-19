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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeLabel;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FranklinAttributeType;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeAddedToFamily;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeCreated;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository\FranklinAttributeAddedToFamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository\FranklinAttributeCreatedRepositoryInterface;

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
        $this->validate($command);

        $pimAttributeType = $command->getFranklinAttributeType()->convertToPimAttributeType();
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

    private function validate(CreateAttributeInFamilyCommand $command): void
    {
        if (FranklinAttributeType::METRIC_TYPE === (string) $command->getFranklinAttributeType()) {
            throw new \InvalidArgumentException(sprintf(
                'Can not create attribute. Attribute of type "%s" is not allowed',
                FranklinAttributeType::METRIC_TYPE
            ));
        }
    }
}
