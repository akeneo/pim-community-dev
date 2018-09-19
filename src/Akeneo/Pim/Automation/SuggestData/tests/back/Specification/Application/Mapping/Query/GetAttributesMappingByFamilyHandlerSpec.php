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
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributesMappingByFamilyHandler;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Query\GetAttributesMappingByFamilyQuery;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\AttributesMappingResponse;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use PhpSpec\ObjectBehavior;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class GetAttributesMappingByFamilyHandlerSpec extends ObjectBehavior
{
    public function let(
        DataProviderFactory $dataProviderFactory,
        DataProviderInterface $dataProvider,
        FamilyRepositoryInterface $familyRepository
    ) {
        $this->beConstructedWith($dataProviderFactory, $familyRepository);
        $dataProviderFactory->create()->willReturn($dataProvider);
    }

    public function it_is_a_get_attributes_mapping_query_handler()
    {
        $this->shouldHaveType(GetAttributesMappingByFamilyHandler::class);
    }

    public function it_throws_an_exception_if_the_family_does_not_exist($familyRepository, $dataProvider)
    {
        $familyRepository->findOneByIdentifier('unknown_family')->willReturn(null);
        $dataProvider->getAttributesMapping('unknown_family')->shouldNotBeCalled();

        $query = new GetAttributesMappingByFamilyQuery('unknown_family');
        $this->shouldThrow(\InvalidArgumentException::class)->during('handle', [$query]);
    }

    public function it_handles_a_get_attributes_mapping_query(FamilyInterface $family, $familyRepository, $dataProvider)
    {
        $attributesMappingResponse = new AttributesMappingResponse();

        $familyRepository->findOneByIdentifier('camcorders')->willReturn($family);
        $dataProvider->getAttributesMapping('camcorders')->willReturn($attributesMappingResponse);

        $query = new GetAttributesMappingByFamilyQuery('camcorders');
        $this->handle($query)->shouldReturn($attributesMappingResponse);
    }
}
