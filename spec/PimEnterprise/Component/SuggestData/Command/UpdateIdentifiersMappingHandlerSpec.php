<?php

declare(strict_types=1);

namespace spec\PimEnterprise\Component\SuggestData\Command;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PimEnterprise\Component\SuggestData\Command\UpdateIdentifiersMapping;
use PimEnterprise\Component\SuggestData\Command\UpdateIdentifiersMappingHandler;
use PhpSpec\ObjectBehavior;
use PimEnterprise\Component\SuggestData\Model\IdentifiersMapping;
use PimEnterprise\Component\SuggestData\Repository\IdentifiersMappingRepositoryInterface;
use Prophecy\Argument;

class UpdateIdentifiersMappingHandlerSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository, IdentifiersMappingRepositoryInterface $identifiersMappingRepository)
    {
        $this->beConstructedWith($attributeRepository, $identifiersMappingRepository);
    }

    function it_is_a_update_identifiers_mapping_handler()
    {
        $this->shouldHaveType(UpdateIdentifiersMappingHandler::class);
    }

    function it_should_throw_an_exception_with_invalid_attributes(
        AttributeRepositoryInterface $attributeRepository,
        UpdateIdentifiersMapping $command
    )
    {
        $attributeRepository->findBy(Argument::cetera())->willReturn([]);
        $command->getIdentifiersMapping()->willReturn([
            'brand' => 'manufacturer',
            'mpn' => 'model',
            'upc' => 'ean',
            'asin' => 'id',
        ]);

        $this->shouldThrow(new \InvalidArgumentException('Some attributes for the identifiers mapping don\'t exist'))->during('handle', [$command]);
    }

    function it_should_save_the_identifiers_mapping(
        AttributeRepositoryInterface $attributeRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        UpdateIdentifiersMapping $command
    )
    {
        $identifiers = [
            'brand' => 'manufacturer',
            'mpn' => 'model',
            'upc' => 'ean',
            'asin' => 'id',
        ];
        $command->getIdentifiersMapping()->willReturn($identifiers);

        $attributeRepository->findBy(Argument::cetera())->willReturn($identifiers);

        $identifiersMapping = new IdentifiersMapping($identifiers);
        $identifiersMappingRepository->save($identifiersMapping)->shouldBeCalled();

        $this->handle($command);
    }
}
