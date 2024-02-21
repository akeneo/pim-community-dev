<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\GenerateIdentifierCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property\GenerateAutoNumberHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property\GenerateFreeTextHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Query\GetNextIdentifierQuery;
use PhpSpec\ObjectBehavior;

class GenerateIdentifierHandlerSpec extends ObjectBehavior
{
    public function let(
        GetNextIdentifierQuery $getNextIdentifierQuery
    ): void {
        $this->beConstructedWith(new \ArrayIterator([
            new GenerateAutoNumberHandler($getNextIdentifierQuery->getWrappedObject()),
            new GenerateFreeTextHandler(),
        ]));
    }

    public function it_should_generate_an_identifier_without_delimiter(
        GetNextIdentifierQuery $getNextIdentifierQuery,
    ): void {
        $identifierGenerator = $this->getIdentifierGenerator(Delimiter::fromString(null));
        $generateIdentifierCommand = GenerateIdentifierCommand::fromIdentifierGenerator(
            $identifierGenerator,
            new ProductProjection(true, null, [], []),
        );

        $getNextIdentifierQuery
            ->fromPrefix($identifierGenerator, 'aKn-', 0)
            ->shouldBeCalled()
            ->willReturn(43);

        $this->__invoke($generateIdentifierCommand)->shouldReturn('aKn-43');
    }

    public function it_should_generate_an_identifier_with_delimiter(
        GetNextIdentifierQuery $getNextIdentifierQuery,
    ): void {
        $identifierGenerator = $this->getIdentifierGenerator(Delimiter::fromString('a'));
        $generateIdentifierCommand = GenerateIdentifierCommand::fromIdentifierGenerator(
            $identifierGenerator,
            new ProductProjection(true, null, [], []),
        );

        $getNextIdentifierQuery
            ->fromPrefix($identifierGenerator, 'aKn-a', 0)
            ->shouldBeCalled()
            ->willReturn(43);

        $this->__invoke($generateIdentifierCommand)->shouldReturn('aKn-a43');
    }

    public function it_should_generate_an_identifier_with_delimiter_and_uppercase(
        GetNextIdentifierQuery $getNextIdentifierQuery,
    ): void {
        $identifierGenerator = $this->getIdentifierGenerator(Delimiter::fromString('x'), 'uppercase');
        $generateIdentifierCommand = GenerateIdentifierCommand::fromIdentifierGenerator(
            $identifierGenerator,
            new ProductProjection(true, null, [], []),
        );

        $getNextIdentifierQuery
            ->fromPrefix($identifierGenerator, 'AKN-X', 0)
            ->shouldBeCalled()
            ->willReturn(43);

        $this->__invoke($generateIdentifierCommand)->shouldReturn('AKN-X43');
    }

    private function getIdentifierGenerator(
        Delimiter $delimiter,
        string $textTransformation = 'no',
    ): IdentifierGenerator
    {
        return new IdentifierGenerator(
            IdentifierGeneratorId::fromString('d556e59e-d46c-465e-863d-f4a39d0b7485'),
            IdentifierGeneratorCode::fromString('my_generator'),
            Conditions::fromArray([]),
            Structure::fromArray([
                FreeText::fromString('aKn-'),
                AutoNumber::fromNormalized([
                    'type' => AutoNumber::type(),
                    'numberMin' => 0,
                    'digitsMin' => 1,
                ]),
            ]),
            LabelCollection::fromNormalized(['en_US' => 'MyGenerator']),
            Target::fromString('sku'),
            $delimiter,
            TextTransformation::fromString($textTransformation),
        );
        $generateIdentifierCommand = GenerateIdentifierCommand::fromIdentifierGenerator(
            $identifierGenerator,
            new ProductProjection(true, null, [], [])
        );

        $getNextIdentifierQuery
            ->fromPrefix($identifierGenerator, 'AKN-', 0)
            ->shouldBeCalled()
            ->willReturn(43);

        $this->__invoke($generateIdentifierCommand)->shouldReturn('AKN-43');
    }
}
