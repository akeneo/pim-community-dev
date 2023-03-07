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
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use PhpSpec\ObjectBehavior;

class GenerateFreeTextHandlerSpec extends ObjectBehavior
{
    public function let(): void
    {
    }

    public function it_should_support_only_free_text(): void
    {
        $this->getPropertyClass()->shouldReturn(FreeText::class);
    }

    public function it_should_throw_exception_when_invoked_with_something_else_than_free_text(): void
    {
        $identifierGenerator = new IdentifierGenerator(
            IdentifierGeneratorId::fromString('d556e59e-d46c-465e-863d-f4a39d0b7485'),
            IdentifierGeneratorCode::fromString('my_generator'),
            Conditions::fromArray([]),
            Structure::fromArray([
                FreeText::fromNormalized([
                    'type' => FreeText::type(),
                    'string' => 'AKN-',
                ]),
            ]),
            LabelCollection::fromNormalized(['en_US' => 'MyGenerator']),
            Target::fromString('sku'),
            Delimiter::fromString(null),
            TextTransformation::fromString('no'),
        );

        $autoNumber = AutoNumber::fromNormalized([
            'type' => AutoNumber::type(),
            'numberMin' => 0,
            'digitsMin' => 1,
        ]);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('__invoke', [
                $autoNumber,
                $identifierGenerator,
                new ProductProjection(true, null, [], []),
                'AKN-'
            ]);
    }

    public function it_should_return_string(): void
    {
        $freeText = FreeText::fromNormalized([
            'type' => FreeText::type(),
            'string' => 'AKN-',
        ]);

        $identifierGenerator = new IdentifierGenerator(
            IdentifierGeneratorId::fromString('d556e59e-d46c-465e-863d-f4a39d0b7485'),
            IdentifierGeneratorCode::fromString('my_generator'),
            Conditions::fromArray([]),
            Structure::fromArray([$freeText]),
            LabelCollection::fromNormalized(['en_US' => 'MyGenerator']),
            Target::fromString('sku'),
            Delimiter::fromString(null),
            TextTransformation::fromString('no'),
        );

        $this->__invoke(
            $freeText,
            $identifierGenerator,
            new ProductProjection(true, null, [], []),
            'AKN-'
        )->shouldReturn('AKN-');
    }
}
