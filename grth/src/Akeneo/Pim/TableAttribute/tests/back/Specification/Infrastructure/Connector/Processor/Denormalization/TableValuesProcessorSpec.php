<?php
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\Processor\Denormalization;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Pim\TableAttribute\Domain\Value\Row;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\DTO\TableRow;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\Processor\Denormalization\TableValuesProcessor;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;

class TableValuesProcessorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $repository,
        GetAttributes $getAttributes,
        StepExecution $stepExecution
    ) {
        $getAttributes->forCode('nutrition')
            ->willReturn(new Attribute(
                'nutrition',
                AttributeTypes::TABLE,
                [],
                false,
                false,
                null,
                null,
                null,
                AttributeTypes::BACKEND_TYPE_TABLE,
                []
            ));

        $this->beConstructedWith($repository, $getAttributes);
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(TableValuesProcessor::class);
        $this->shouldImplement(ItemProcessorInterface::class);
    }

    function it_should_throw_exception_with_unknown_entity(IdentifiableObjectRepositoryInterface $repository)
    {
        $repository->findOneByIdentifier('unknown_entity')
            ->shouldBeCalled()
            ->willReturn(null);

        $item = [
            'entity' => 'unknown_entity',
            'attribute_code' => 'nutrition',
            'locale' => null,
            'scope' => null,
            'row_values' => [],
        ];

        $this->shouldThrow(InvalidItemException::class)->during('process', [$item]);
    }

    function it_should_throw_exception_with_unkown_attribute(
        IdentifiableObjectRepositoryInterface $repository,
        GetAttributes $getAttributes,
        ProductInterface $product
    ) {
        $repository->findOneByIdentifier('11111')
            ->shouldBeCalled()
            ->willReturn($product);

        $getAttributes->forCode('unknown_attribute')
            ->shouldBeCalled()
            ->willReturn(null);

        $item = [
            'entity' => '11111',
            'attribute_code' => 'unknown_attribute',
            'locale' => null,
            'scope' => null,
            'row_values' => [],
        ];

        $this->shouldThrow(InvalidItemException::class)->during('process', [$item]);
    }

    function it_should_throw_exception_with_non_table_attribute(
        IdentifiableObjectRepositoryInterface $repository,
        GetAttributes $getAttributes,
        ProductInterface $product
    ) {
        $repository->findOneByIdentifier('11111')
            ->shouldBeCalled()
            ->willReturn($product);

        $getAttributes->forCode('a_text')
            ->willReturn(new Attribute(
                'a_text',
                AttributeTypes::TEXT,
                [],
                false,
                false,
                null,
                null,
                null,
                AttributeTypes::BACKEND_TYPE_TEXT,
                []
            ));

        $item = [
            'entity' => '11111',
            'attribute_code' => 'a_text',
            'locale' => null,
            'scope' => null,
            'row_values' => [],
        ];

        $this->shouldThrow(InvalidItemException::class)->during('process', [$item]);
    }

    function it_denormalizes_a_table_row(IdentifiableObjectRepositoryInterface $repository, ProductInterface $product)
    {
        $repository->findOneByIdentifier('11111')
            ->shouldBeCalled()
            ->willReturn($product);

        $item = [
            'entity' => '11111',
            'attribute_code' => 'nutrition',
            'locale' => 'en_US',
            'scope' => 'mobile',
            'row_values' => [
                'ingredient' => 'salt',
                'quantity' => '50',
                'allergenic' => false,
                'length' => [
                    'unit' => 'CENTIMETER',
                    'amount' => '50',
                ],
            ],
        ];

        $this->process($item)->shouldBeLike(new TableRow(
            '11111',
            'nutrition',
            'en_US',
            'mobile',
            Row::fromNormalized([
                'ingredient' => 'salt',
                'quantity' => '50',
                'allergenic' => false,
                'length' => [
                    'unit' => 'CENTIMETER',
                    'amount' => '50',
                ],
            ])
        ));
    }
}
