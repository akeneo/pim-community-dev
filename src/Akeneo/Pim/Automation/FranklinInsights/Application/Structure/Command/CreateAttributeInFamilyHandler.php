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

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class CreateAttributeInFamilyHandler
{
    /** @var CreateAttributeInterface */
    private $createAttribute;

    /** @var AddAttributeToFamilyInterface */
    private $updateFamily;

    /**
     * @param CreateAttributeInterface $createAttribute
     * @param AddAttributeToFamilyInterface $updateFamily
     */
    public function __construct(
        CreateAttributeInterface $createAttribute,
        AddAttributeToFamilyInterface $updateFamily
    ) {
        $this->createAttribute = $createAttribute;
        $this->updateFamily = $updateFamily;
    }

    public function handle(CreateAttributeInFamilyCommand $command): void
    {
        $this->createAttribute->create(
            $command->getPimAttributeCode(),
            new AttributeLabel((string) $command->getFranklinAttributeLabel()),
            $command->getFranklinAttributeType()->convertToPimAttributeType()
        );

        $this->updateFamily->addAttributeToFamily($command->getPimAttributeCode(), $command->getPimFamilyCode());
    }
}
