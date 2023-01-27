<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UnableToTruncateException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FamilyProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\PropertyInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use PhpSpec\ObjectBehavior;

class GenerateFamilyHandlerSpec extends ObjectBehavior
{
    public function let(): void
    {
    }

    public function it_should_support_only_family_property(): void
    {
        $this->getPropertyClass()->shouldReturn(FamilyProperty::class);
    }

    public function it_should_throw_exception_when_invoked_with_something_else_than_family_property(): void
    {
        $autoNumber = AutoNumber::fromNormalized([
            'type' => AutoNumber::type(),
            'numberMin' => 0,
            'digitsMin' => 1,
        ]);

        $identifierGenerator = $this->getIdentifierGenerator($autoNumber);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('__invoke', [
                $autoNumber,
                $identifierGenerator,
                new ProductProjection(null, true, null, []),
                'AKN-'
            ]);
    }

    public function it_should_return_family_code_without_truncate(): void
    {
        $family = FamilyProperty::fromNormalized([
            'type' => FamilyProperty::type(),
            'process' => [
                'type' => 'no'
            ]
        ]);

        $identifierGenerator = $this->getIdentifierGenerator($family);

        $this->__invoke(
            $family,
            $identifierGenerator,
            $this->getProductProjection('familyCode'),
            'AKN-'
        )->shouldReturn('familyCode');
    }

    public function it_should_return_family_code_with_truncate(): void
    {
        $family = FamilyProperty::fromNormalized([
            'type' => FamilyProperty::type(),
            'process' => [
                'type' => 'truncate',
                'operator' => Process::PROCESS_OPERATOR_LTE,
                'value' => 3,
            ]
        ]);

        $identifierGenerator = $this->getIdentifierGenerator($family);

        $this->__invoke(
            $family,
            $identifierGenerator,
            $this->getProductProjection('familyCode'),
            'AKN-'
        )->shouldReturn('fam');
    }

    public function it_should_return_family_code_with_truncate_and_smaller_family_code(): void
    {
        $family = FamilyProperty::fromNormalized([
            'type' => FamilyProperty::type(),
            'process' => [
                'type' => 'truncate',
                'operator' => Process::PROCESS_OPERATOR_LTE,
                'value' => 3,
            ]
        ]);

        $identifierGenerator = $this->getIdentifierGenerator($family);

        $this->__invoke(
            $family,
            $identifierGenerator,
            $this->getProductProjection('fa'),
            'AKN-'
        )->shouldReturn('fa');
    }

    public function it_should_throw_an_error_if_family_code_is_too_small(): void
    {
        $family = FamilyProperty::fromNormalized([
            'type' => FamilyProperty::type(),
            'process' => [
                'type' => 'truncate',
                'operator' => Process::PROCESS_OPERATOR_EQ,
                'value' => 4,
            ]
        ]);

        $identifierGenerator = $this->getIdentifierGenerator($family);

        $this->shouldThrow(new UnableToTruncateException('AKN-fam', 'sku', 'fam'))->during(
            '__invoke', [
                $family,
                $identifierGenerator,
                $this->getProductProjection('fam'),
                'AKN-'
            ]
        );
    }

    public function it_should_not_throw_an_error_if_family_code_is_exactly_the_right_length(): void
    {
        $family = FamilyProperty::fromNormalized([
            'type' => FamilyProperty::type(),
            'process' => [
                'type' => 'truncate',
                'operator' => Process::PROCESS_OPERATOR_EQ,
                'value' => 3,
            ]
        ]);

        $identifierGenerator = $this->getIdentifierGenerator($family);

        $this->__invoke(
            $family,
            $identifierGenerator,
            $this->getProductProjection('fam'),
            'AKN-'
        )->shouldReturn('fam');
    }

    private function getProductProjection(string $familyCode): ProductProjection
    {
        return new ProductProjection(null, true, $familyCode, []);
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
