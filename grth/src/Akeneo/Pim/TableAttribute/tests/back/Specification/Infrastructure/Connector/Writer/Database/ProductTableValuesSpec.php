<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\Writer\Database;

use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\BooleanColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\NumberColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\Value\Row;
use Akeneo\Pim\TableAttribute\Domain\Value\Table;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\DTO\TableRow;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\Writer\Database\ProductTableValues;
use Akeneo\Pim\TableAttribute\Infrastructure\Normalizer\Standard\TableNormalizer;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Akeneo\Test\Pim\TableAttribute\Helper\ColumnIdGenerator;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductTableValuesSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $entityRepository,
        AttributeRepositoryInterface $attributeRepository,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        TableNormalizer $tableNormalizer,
        TableConfigurationRepository $tableConfigurationRepository,
        ValidatorInterface $validator,
        BulkSaverInterface $bulkSaver,
        StepExecution $stepExecution
    ) {
        $tableConfigurationRepository->getByAttributeCode('nutrition')->willReturn(TableConfiguration::fromColumnDefinitions([
            SelectColumn::fromNormalized(['id' => ColumnIdGenerator::ingredient(), 'code' => 'ingredient', 'is_required_for_completeness' => true]),
            NumberColumn::fromNormalized(['id' => ColumnIdGenerator::quantity(), 'code' => 'quantity']),
            BooleanColumn::fromNormalized(['id' => ColumnIdGenerator::isAllergenic(), 'code' => 'allergenic']),
        ]));

        $this->beConstructedWith(
            $entityRepository,
            $attributeRepository,
            $entityWithValuesBuilder,
            $tableNormalizer,
            $tableConfigurationRepository,
            $validator,
            $bulkSaver
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductTableValues::class);
        $this->shouldImplement(ItemWriterInterface::class);
        $this->shouldImplement(StepExecutionAwareInterface::class);
    }

    function it_only_writes_table_rows()
    {
        $tableRow = new TableRow('111', 'nutrition', null, null, Row::fromNormalized(['ingredient' => 'salt']));

        $this->shouldThrow(\InvalidArgumentException::class)->during('write', [
            [$tableRow, 'other'],
        ]);
    }

    function it_writes_some_table_rows(
        AttributeRepositoryInterface $attributeRepository,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        IdentifiableObjectRepositoryInterface $entityRepository,
        EntityWithValuesInterface $entityWithValues1,
        EntityWithValuesInterface $entityWithValues2,
        TableNormalizer $tableNormalizer,
        TableValue $formerTableValue,
        TableValue $updatedTableValue,
        ValidatorInterface $validator,
        BulkSaverInterface $bulkSaver,
        StepExecution $stepExecution,
        AttributeInterface $attribute
    ) {
        $tableRows = [
            new TableRow('111', 'nutrition', null, null, Row::fromNormalized([
                'ingredient' => 'salt',
                'quantity' => '24',
                'allergenic' => true,
            ])),
            new TableRow('111', 'nutrition', null, null, Row::fromNormalized([
                'ingredient' => 'sugar',
                'quantity' => '42',
                'allergenic' => false,
            ])),
            new TableRow('112', 'nutrition', null, null, Row::fromNormalized([
                'ingredient' => 'pepper',
                'quantity' => '42',
            ])),
        ];

        $attributeRepository->findOneByIdentifier('nutrition')->willReturn($attribute);

        $entityWithValues1->getValue('nutrition', null, null)->WillReturn($formerTableValue, $updatedTableValue);
        $entityRepository->findOneByIdentifier('111')->willReturn($entityWithValues1);
        $entityWithValues2->getValue('nutrition', null, null)->willReturn(null);
        $entityRepository->findOneByIdentifier('112')->willReturn($entityWithValues2);

        // First TableRow
        $formerTable = $this->createRandomTable();
        $formerTableValue->getData()->willReturn($formerTable);
        $tableNormalizer->normalize($formerTable)->willReturn([
            ['ingredient' => 'salt', 'quantity' => '12', 'allergenic' => false],
        ]);
        $entityWithValuesBuilder->addOrReplaceValue($entityWithValues1, $attribute, null, null, [
            ['ingredient' => 'salt', 'quantity' => '24', 'allergenic' => true],
        ])->shouldBeCalledOnce();

        // Second TableRow
        $updatedTable = $this->createRandomTable();
        $updatedTableValue->getData()->willReturn($updatedTable);
        $tableNormalizer->normalize($updatedTable)->willReturn([
            ['ingredient' => 'salt', 'quantity' => '24', 'allergenic' => true],
        ]);
        $entityWithValuesBuilder->addOrReplaceValue($entityWithValues1, $attribute, null, null, [
            ['ingredient' => 'salt', 'quantity' => '24', 'allergenic' => true],
            ['ingredient' => 'sugar', 'quantity' => '42', 'allergenic' => false],
        ])->shouldBeCalledOnce();

        // Third TableRow
        $entityWithValuesBuilder->addOrReplaceValue($entityWithValues2, $attribute, null, null, [
            ['ingredient' => 'pepper', 'quantity' => '42'],
        ])->shouldBeCalledOnce();

        $validator->validate($entityWithValues1)->shouldBeCalledTimes(2)->willReturn(new ConstraintViolationList());
        $stepExecution->incrementSummaryInfo('update', 2)->shouldBeCalledOnce();
        $validator->validate($entityWithValues2)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList());
        $stepExecution->incrementSummaryInfo('update', 1)->shouldBeCalledOnce();

        $bulkSaver->saveAll([$entityWithValues1, $entityWithValues2])->shouldBeCalledOnce();

        $this->write($tableRows);
    }

    function it_writes_some_product_table_rows_with_locale(
        AttributeRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $entityRepository,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        EntityWithValuesInterface $entityWithValues,
        TableNormalizer $tableNormalizer,
        TableValue $formerTableValue,
        ValidatorInterface $validator,
        BulkSaverInterface $bulkSaver,
        StepExecution $stepExecution,
        AttributeInterface $attribute
    ) {
        $tableRows = [
            new TableRow('111', 'nutrition', 'en_US', null, Row::fromNormalized([
                'ingredient' => 'salt',
                'quantity' => '24',
                'allergenic' => true,
            ])),
            new TableRow('111', 'nutrition', 'fr_FR', null, Row::fromNormalized([
                'ingredient' => 'sugar',
                'quantity' => '42',
                'allergenic' => false,
            ])),
        ];

        $attributeRepository->findOneByIdentifier('nutrition')->willReturn($attribute);

        $entityWithValues->getValue('nutrition', 'en_US', null)->WillReturn($formerTableValue);
        $entityWithValues->getValue('nutrition', 'fr_FR', null)->willReturn(null);
        $entityRepository->findOneByIdentifier('111')->willReturn($entityWithValues);

        // First TableRow
        $formerTable = $this->createRandomTable();
        $formerTableValue->getData()->willReturn($formerTable);
        $tableNormalizer->normalize($formerTable)->willReturn([
            ['ingredient' => 'salt', 'quantity' => '12', 'allergenic' => false],
        ]);
        $entityWithValuesBuilder->addOrReplaceValue($entityWithValues, $attribute, 'en_US', null, [
            ['ingredient' => 'salt', 'quantity' => '24', 'allergenic' => true],
        ])->shouldBeCalledOnce();

        // Second TableRow
        $entityWithValuesBuilder->addOrReplaceValue($entityWithValues, $attribute, 'fr_FR', null, [
            ['ingredient' => 'sugar', 'quantity' => '42', 'allergenic' => false],
        ])->shouldBeCalledOnce();

        $validator->validate($entityWithValues)->shouldBeCalledTimes(2)->willReturn(new ConstraintViolationList());
        $stepExecution->incrementSummaryInfo('update', 2)->shouldBeCalledOnce();

        $bulkSaver->saveAll([$entityWithValues])->shouldBeCalledOnce();

        $this->write($tableRows);
    }

    function it_does_not_save_product_if_the_validation_fails(
        AttributeRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $entityRepository,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        EntityWithValuesInterface $entityWithValues,
        TableNormalizer $tableNormalizer,
        TableValue $formerTableValue,
        TableValue $newValue,
        ValidatorInterface $validator,
        BulkSaverInterface $bulkSaver,
        StepExecution $stepExecution,
        AttributeInterface $attribute
    ) {
        $tableRows = [
            new TableRow('111', 'nutrition', null, null, Row::fromNormalized([
                'ingredient' => 'salt',
                'quantity' => '24',
                'allergenic' => true,
            ])),
        ];

        $attributeRepository->findOneByIdentifier('nutrition')->willReturn($attribute);

        $entityWithValues->getValue('nutrition', null, null)->WillReturn($formerTableValue);
        $entityRepository->findOneByIdentifier('111')->willReturn($entityWithValues);

        // First TableRow
        $formerTable = $this->createRandomTable();
        $formerTableValue->getData()->willReturn($formerTable);
        $tableNormalizer->normalize($formerTable)->willReturn([
            ['ingredient' => 'salt', 'quantity' => '12', 'allergenic' => false],
        ]);
        $entityWithValuesBuilder->addOrReplaceValue($entityWithValues, $attribute, null, null, [
            ['ingredient' => 'salt', 'quantity' => '24', 'allergenic' => true],
        ])->shouldBeCalledOnce()->willReturn($newValue);

        $validator->validate($entityWithValues)->shouldBeCalledOnce()->willReturn(new ConstraintViolationList([
            new ConstraintViolation('Error1', '', [], null, null, null),
            new ConstraintViolation('Error2', '', [], null, null, null),
        ]));
        $stepExecution->addWarning('Error1, Error2', [], Argument::any())->shouldBeCalledOnce();
        $stepExecution->incrementSummaryInfo('update', Argument::cetera())->shouldNotBeCalled();
        $stepExecution->incrementSummaryInfo('skip', Argument::cetera())->shouldBeCalledOnce();
        $entityWithValues->removeValue($newValue)->shouldBeCalledOnce();
        $entityWithValues->addValue($formerTableValue)->shouldBeCalledOnce();

        $bulkSaver->saveAll([])->shouldBeCalledOnce();
        $bulkSaver->saveAll([$entityWithValues])->shouldNotBeCalled();

        $this->write($tableRows);
    }

    function it_does_not_save_an_unknown_product(IdentifiableObjectRepositoryInterface $entityRepository)
    {
        $tableRows = [
            new TableRow('111', 'nutrition', null, null, Row::fromNormalized([
                'ingredient' => 'salt',
                'quantity' => '24',
                'allergenic' => true,
            ])),
        ];
        $entityRepository->findOneByIdentifier('111')->willReturn(null);

        $this->shouldThrow(\InvalidArgumentException::class)->during('write', [$tableRows]);
    }

    private function createRandomTable(): Table
    {
        return Table::fromNormalized([['ingredient' => uniqid()]]);
    }
}
