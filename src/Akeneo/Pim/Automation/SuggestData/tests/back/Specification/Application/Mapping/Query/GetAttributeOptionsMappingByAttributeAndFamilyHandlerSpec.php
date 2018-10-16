<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query;

use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderFactory;
use Akeneo\Pim\Automation\SuggestData\Application\DataProvider\DataProviderInterface;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributeOptionsMappingByAttributeAndFamilyHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributeOptionsMappingByAttributeAndFamilyQuery;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\FamilyCode;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\FranklinAttributeId;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class GetAttributeOptionsMappingByAttributeAndFamilyHandlerSpec extends ObjectBehavior
{
    public function let(
        DataProviderFactory $dataProviderFactory,
        DataProviderInterface $dataProvider,
        FamilyRepositoryInterface $familyRepository
    ): void {
        $dataProviderFactory->create()->willReturn($dataProvider);

        $this->beConstructedWith($dataProviderFactory, $familyRepository);
    }

    public function it_is_a_get_attribute_option_mapping_handler(): void
    {
        $this->shouldBeAnInstanceOf(GetAttributeOptionsMappingByAttributeAndFamilyHandler::class);
    }

    public function it_throws_an_exception_when_the_family_does_not_exist($familyRepository): void
    {
        $familyCode = new FamilyCode('foo');
        $franklinAttributeId = new FranklinAttributeId('bar');
        $query = new GetAttributeOptionsMappingByAttributeAndFamilyQuery($familyCode, $franklinAttributeId);

        $familyRepository->findOneByIdentifier($familyCode)->willReturn(null);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('handle', [$query]);
    }
}
