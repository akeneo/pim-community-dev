<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\Writer\Database;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\DTO\SelectOptionDetails;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOptionCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\SelectOptionCode;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\Writer\Database\SelectOption;
use Akeneo\Tool\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class SelectOptionSpec extends ObjectBehavior
{
    function let(SelectOptionCollectionRepository $selectOptionCollectionRepository, StepExecution $stepExecution)
    {
        $this->beConstructedWith($selectOptionCollectionRepository);
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(ItemWriterInterface::class);
        $this->shouldImplement(StepExecutionAwareInterface::class);
        $this->shouldHaveType(SelectOption::class);
    }

    function it_saves_select_options(
        SelectOptionCollectionRepository $selectOptionCollectionRepository,
        StepExecution $stepExecution
    ) {
        $items = [
            new SelectOptionDetails('nutrition', 'ingredient', 'salt', ['en_US' => 'Salt']),
            new SelectOptionDetails('nutrition', 'grade', 'a', ['en_US' => 'A']),
            new SelectOptionDetails('nutrition', 'ingredient', 'sugar', ['en_US' => 'Sugar']),
            new SelectOptionDetails('packaging', 'dimension', 'width', ['en_US' => 'Width']),
        ];

        $selectOptionCollectionRepository->upsert(
            'nutrition',
            ColumnCode::fromString('ingredient'),
            Argument::that(
                fn ($options) => $options instanceof SelectOptionCollection && [
                        'salt',
                        'sugar',
                    ] === $this->convertToString($options->getOptionCodes())
            )
        )->shouldBeCalledOnce();
        $selectOptionCollectionRepository->upsert(
            'nutrition',
            ColumnCode::fromString('grade'),
            Argument::that(
                fn ($options) => $options instanceof SelectOptionCollection && ['a'] === $this->convertToString($options->getOptionCodes())
            )
        )->shouldBeCalledOnce();
        $selectOptionCollectionRepository->upsert(
            'packaging',
            ColumnCode::fromString('dimension'),
            Argument::that(
                fn ($options) => $options instanceof SelectOptionCollection && ['width'] === $this->convertToString($options->getOptionCodes())
            )
        )->shouldBeCalledOnce();

        $stepExecution->incrementSummaryInfo('update', 2)->shouldBeCalledOnce();
        $stepExecution->incrementSummaryInfo('update', 1)->shouldBeCalledTimes(2);

        $this->write($items);
    }

    function it_appends_new_select_options(
        SelectOptionCollectionRepository $selectOptionCollectionRepository,
        StepExecution $stepExecution
    ) {
        $items = [
            new SelectOptionDetails('nutrition', 'ingredient', 'salt', ['en_US' => 'Salt']),
        ];

        $selectOptionCollectionRepository->upsert(
            'nutrition',
            Argument::that(
                fn ($columnCode): bool => $columnCode instanceof ColumnCode && 'ingredient' === $columnCode->asString()
            ),
            Argument::that(
                fn ($options) => $options instanceof SelectOptionCollection && [
                        [
                            'code' => 'salt',
                            'labels' => ['en_US' => 'Salt'],
                        ],
                    ] === $options->normalize()
            )
        )->shouldBeCalledOnce();

        $stepExecution->incrementSummaryInfo('update', 1)->shouldBeCalledOnce();

        $this->write($items);
    }

    function it_only_handles_select_option_details()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('write', [[new \stdClass()]]);
    }

    /**
     * @param SelectOptionCode[] $selectOptionCodes
     * @return string[]
     */
    private function convertToString(array $selectOptionCodes): array
    {
        return array_map(
            fn (SelectOptionCode $selectOptionCode): string => $selectOptionCode->asString(),
            $selectOptionCodes
        );
    }
}
