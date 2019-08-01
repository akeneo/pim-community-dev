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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command;

use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Command\BulkAddAttributesToFamilyCommand;
use Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service\AddAttributeToFamilyInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\AttributeCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Event\FranklinAttributeAddedToFamily;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository\FranklinAttributeAddedToFamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class BulkAddAttributesToFamilyHandlerSpec extends ObjectBehavior
{
    public function let(
        AddAttributeToFamilyInterface $updateFamily,
        FranklinAttributeAddedToFamilyRepositoryInterface $franklinAttributeAddedToFamilyRepository
    ): void
    {
        $this->beConstructedWith($updateFamily, $franklinAttributeAddedToFamilyRepository);
    }

    public function it_attaches_an_attribute_to_a_family(
        AddAttributeToFamilyInterface $updateFamily,
        FranklinAttributeAddedToFamilyRepositoryInterface $franklinAttributeAddedToFamilyRepository
    ): void
    {
        $familyCode = new FamilyCode('family_code');
        $attributeCodes = [new AttributeCode('color'), new AttributeCode('height')];

        $command = new BulkAddAttributesToFamilyCommand($familyCode, $attributeCodes);

        $updateFamily
            ->bulkAddAttributesToFamily($familyCode, $attributeCodes)
            ->shouldBeCalled();

        $franklinAttributeAddedToFamilyRepository
            ->saveAll(Argument::that(function($events) {
                return empty(array_filter($events, function ($event) {
                    return ! $event instanceof FranklinAttributeAddedToFamily;
                }));
            }))
            ->shouldBeCalled();

        $this->handle($command);
    }
}
