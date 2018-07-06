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

namespace spec\Akeneo\Pim\Automation\SuggestData\Component\Command;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Automation\SuggestData\Component\Command\UpdateIdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Component\Command\UpdateIdentifiersMappingHandler;
use Akeneo\Pim\Automation\SuggestData\Component\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Component\Repository\IdentifiersMappingRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UpdateIdentifiersMappingHandlerSpec extends ObjectBehavior
{
    function let(AttributeRepositoryInterface $attributeRepository, IdentifiersMappingRepositoryInterface $identifiersMappingRepository)
    {
        $this->beConstructedWith($attributeRepository, $identifiersMappingRepository);
    }

    function it_is_an_update_identifiers_mapping_handler()
    {
        $this->shouldHaveType(UpdateIdentifiersMappingHandler::class);
    }

    function it_should_throw_an_exception_with_invalid_attributes(
        AttributeRepositoryInterface $attributeRepository,
        UpdateIdentifiersMapping $command
    ) {
        $attributeRepository->findOneByIdentifier(Argument::any())->willReturn(null);
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
        UpdateIdentifiersMapping $command,
        AttributeInterface $manufacturer,
        AttributeInterface $model,
        AttributeInterface $ean,
        AttributeInterface $id
    ) {
        $identifiers = [
            'brand' => 'manufacturer',
            'mpn' => 'model',
            'upc' => 'ean',
            'asin' => 'id',
        ];
        $command->getIdentifiersMapping()->willReturn($identifiers);

        $attributeRepository->findOneByIdentifier(Argument::any())->shouldBeCalledTimes(4);
        $attributeRepository->findOneByIdentifier('manufacturer')->willReturn($manufacturer);
        $attributeRepository->findOneByIdentifier('model')->willReturn($model);
        $attributeRepository->findOneByIdentifier('ean')->willReturn($ean);
        $attributeRepository->findOneByIdentifier('id')->willReturn($id);

        $identifiersMapping = new IdentifiersMapping([
            'brand' => $manufacturer->getWrappedObject(),
            'mpn' => $model->getWrappedObject(),
            'upc' => $ean->getWrappedObject(),
            'asin' => $id->getWrappedObject(),
        ]);
        $identifiersMappingRepository->save($identifiersMapping)->shouldBeCalled();

        $this->handle($command);
    }
}
