<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\PropertyInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Query\GetNextIdentifierQuery;
use PhpSpec\ObjectBehavior;

class GenerateAutoNumberHandlerSpec extends ObjectBehavior
{
    public function let(
        GetNextIdentifierQuery $getNextIdentifierQuery
    ): void {
        $this->beConstructedWith($getNextIdentifierQuery);
    }

    public function it_should_support_only_auto_numbers(): void
    {
        $this->getPropertyClass()->shouldReturn(AutoNumber::class);
    }

    public function it_should_throw_exception_when_invoked_with_something_else_than_auto_number(): void
    {
        $freeText = FreeText::fromNormalized([
            'type' => FreeText::type(),
            'string' => 'AKN-',
        ]);

        $identifierGenerator = $this->getIdentifierGenerator($freeText);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('__invoke', [
                $freeText,
                $identifierGenerator,
                new ProductProjection(true, null, [], []),
                'AKN-'
            ]);
    }

    public function it_should_return_next_number(
        GetNextIdentifierQuery $getNextIdentifierQuery
    ): void {
        $target = Target::fromString('sku');
        $autoNumber = AutoNumber::fromNormalized([
            'type' => AutoNumber::type(),
            'numberMin' => 0,
            'digitsMin' => 1,
        ]);

        $identifierGenerator = $this->getIdentifierGenerator($autoNumber);

        $getNextIdentifierQuery->fromPrefix($identifierGenerator, 'AKN-', 0)
            ->shouldBeCalled()
            ->willReturn(42);

        $this->__invoke(
            $autoNumber,
            $identifierGenerator,
            new ProductProjection(true, null, [], []),
            'AKN-'
        )->shouldReturn('42');
    }

    public function it_should_set_min_number(
        GetNextIdentifierQuery $getNextIdentifierQuery
    ): void {
        $target = Target::fromString('sku');
        $autoNumber = AutoNumber::fromNormalized([
            'type' => AutoNumber::type(),
            'numberMin' => 50,
            'digitsMin' => 1,
        ]);

        $identifierGenerator = $this->getIdentifierGenerator($autoNumber);

        $getNextIdentifierQuery->fromPrefix($identifierGenerator, 'AKN-', 50)
            ->shouldBeCalled()
            ->willReturn(50);

        $this->__invoke(
            $autoNumber,
            $identifierGenerator,
            new ProductProjection(true, null, [], []),
            'AKN-'
        )->shouldReturn('50');
    }

    public function it_should_add_digits_when_number_is_too_low(
        GetNextIdentifierQuery $getNextIdentifierQuery
    ): void {
        $autoNumber = AutoNumber::fromNormalized([
            'type' => AutoNumber::type(),
            'numberMin' => 0,
            'digitsMin' => 5,
        ]);

        $identifierGenerator = $this->getIdentifierGenerator($autoNumber);

        $getNextIdentifierQuery->fromPrefix($identifierGenerator, 'AKN-', 0)
            ->shouldBeCalled()
            ->willReturn(42);

        $this->__invoke(
            $autoNumber,
            $identifierGenerator,
            new ProductProjection(true, null, [], []),
            'AKN-'
        )->shouldReturn('00042');
    }

    public function it_should_not_add_digits_when_number_is_too_high(
        GetNextIdentifierQuery $getNextIdentifierQuery
    ): void {
        $autoNumber = AutoNumber::fromNormalized([
            'type' => AutoNumber::type(),
            'numberMin' => 0,
            'digitsMin' => 5,
        ]);

        $identifierGenerator = $this->getIdentifierGenerator($autoNumber);

        $getNextIdentifierQuery->fromPrefix($identifierGenerator, 'AKN-', 0)
            ->shouldBeCalled()
            ->willReturn(426942);

        $this->__invoke(
            $autoNumber,
            $identifierGenerator,
            new ProductProjection(true, null, [], []),
            'AKN-'
        )->shouldReturn('426942');
    }

    private function getIdentifierGenerator(PropertyInterface $property): IdentifierGenerator
    {
        return new IdentifierGenerator(
            IdentifierGeneratorId::fromString('d556e59e-d46c-465e-863d-f4a39d0b7485'),
            IdentifierGeneratorCode::fromString('my_generator'),
            Conditions::fromArray([]),
            Structure::fromArray([$property]),
            LabelCollection::fromNormalized(['en_US' => 'MyGenerator']),
            Target::fromString('sku'),
            Delimiter::fromString(null),
            TextTransformation::fromString('no'),
        );
    }
}
