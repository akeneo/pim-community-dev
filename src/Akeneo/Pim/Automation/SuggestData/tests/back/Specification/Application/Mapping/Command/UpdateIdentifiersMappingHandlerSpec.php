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

namespace Specification\Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command;

use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateIdentifiersMappingCommand;
use Akeneo\Pim\Automation\SuggestData\Application\Mapping\Command\UpdateIdentifiersMappingHandler;
use Akeneo\Pim\Automation\SuggestData\Domain\Exception\InvalidMappingException;
use Akeneo\Pim\Automation\SuggestData\Domain\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\SuggestData\Domain\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class UpdateIdentifiersMappingHandlerSpec extends ObjectBehavior
{
    public function let(AttributeRepositoryInterface $attributeRepository, IdentifiersMappingRepositoryInterface $identifiersMappingRepository)
    {
        $this->beConstructedWith($attributeRepository, $identifiersMappingRepository);
    }

    public function it_is_an_update_identifiers_mapping_handler()
    {
        $this->shouldHaveType(UpdateIdentifiersMappingHandler::class);
    }

    public function it_throws_an_exception_with_invalid_attributes(
        AttributeRepositoryInterface $attributeRepository,
        AttributeInterface $model,
        $identifiersMappingRepository
    ) {
        $command = new UpdateIdentifiersMappingCommand([
            'mpn' => 'model',
            'upc' => null,
            'asin' => null,
            'brand' => 'attributeNotFound',
        ]);

        $attributeRepository->findOneByIdentifier('model')->willReturn($model);
        $attributeRepository->findOneByIdentifier('attributeNotFound')->willReturn(null);
        $attributeRepository->findOneByIdentifier(null)->shouldNotBeCalled();

        $identifiersMappingRepository->save(Argument::any())->shouldNotBeCalled();

        $this->shouldThrow(
            InvalidMappingException::attributeNotFound('attributeNotFound', UpdateIdentifiersMappingHandler::class)
        )->during('handle', [$command]);
    }

    public function it_saves_the_identifiers_mapping(
        AttributeRepositoryInterface $attributeRepository,
        IdentifiersMappingRepositoryInterface $identifiersMappingRepository,
        UpdateIdentifiersMappingCommand $command,
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
