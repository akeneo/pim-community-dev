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
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeAddedToFamily;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository\FranklinAttributeAddedToFamilyRepositoryInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class BulkAddAttributesToFamilyHandler
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

    public function handle(BulkAddAttributesToFamilyCommand $command)
    {
        $this->updateFamily->bulkAddAttributesToFamily($command->getFamilyCode(), $command->getAttributeCodes());

        $franklinAttributeAddedToFamilyEvents = [];
        foreach ($command->getAttributeCodes() as $attributeCode) {
            $franklinAttributeAddedToFamilyEvents[] = new FranklinAttributeAddedToFamily($attributeCode, $command->getFamilyCode());
        }

        $this->franklinAttributeAddedToFamilyRepository->saveAll($franklinAttributeAddedToFamilyEvents);
    }
}
