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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeCreated;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class CreateAttributeInFamilyHandler
{
    /** @var CreateAttributeInterface */
    private $createAttribute;

    /** @var AddAttributeToFamilyInterface */
    private $updateFamily;

    private $eventDispatcher;

    public function __construct(
        CreateAttributeInterface $createAttribute,
        AddAttributeToFamilyInterface $updateFamily,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->createAttribute = $createAttribute;
        $this->updateFamily = $updateFamily;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function handle(CreateAttributeInFamilyCommand $command): void
    {
        $this->validate($command);

        $pimAttributeType = $command->getFranklinAttributeType()->convertToPimAttributeType();
        $this->createAttribute->create(
            $command->getPimAttributeCode(),
            new AttributeLabel((string) $command->getFranklinAttributeLabel()),
            $command->getFranklinAttributeType()->convertToPimAttributeType()
        );
        $this->eventDispatcher->dispatch(
            FranklinAttributeCreated::EVENT_NAME,
            new FranklinAttributeCreated($command->getPimAttributeCode(), $pimAttributeType)
        );

        $this->updateFamily->addAttributeToFamily($command->getPimAttributeCode(), $command->getPimFamilyCode());
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
