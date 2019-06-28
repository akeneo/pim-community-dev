<?php

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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeAddedToFamily;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository\FranklinAttributeAddedToFamilyRepositoryInterface;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class AttachAttributeToFamilyHandler
{
    private $updateFamily;

    private $franklinAttributeAddedToFamilyRepository;

    public function __construct(
        AddAttributeToFamilyInterface $updateFamily,
        FranklinAttributeAddedToFamilyRepositoryInterface $franklinAttributeAddedToFamilyRepository
    ) {
        $this->updateFamily = $updateFamily;
        $this->franklinAttributeAddedToFamilyRepository = $franklinAttributeAddedToFamilyRepository;
    }

    public function handle(AttachAttributeToFamilyCommand $command)
    {
        $this->updateFamily->addAttributeToFamily($command->getPimAttributeCode(), $command->getPimFamilyCode());

        $this->franklinAttributeAddedToFamilyRepository->save(
            new FranklinAttributeAddedToFamily($command->getPimAttributeCode(), $command->getPimFamilyCode())
        );
    }
}
