<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Application\Generate\Property;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UnableToTruncateException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\ProductProjection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\AutoNumber;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FamilyProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\Process;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
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
        $target = Target::fromString('sku');
        $autoNumber = AutoNumber::fromNormalized([
            'type' => AutoNumber::type(),
            'numberMin' => 0,
            'digitsMin' => 1,
        ]);

        $this
            ->shouldThrow(\InvalidArgumentException::class)
            ->during('__invoke', [
                $autoNumber,
                $target,
                new ProductProjection(null, true, null, []),
                'AKN-'
            ]);
    }

    public function it_should_return_family_code_without_truncate(): void
    {
        $target = Target::fromString('sku');
        $family = FamilyProperty::fromNormalized([
            'type' => FamilyProperty::type(),
            'process' => [
                'type' => 'no'
            ]
        ]);

        $this->__invoke(
            $family,
            $target,
            $this->getProductProjection('familyCode'),
            'AKN-'
        )->shouldReturn('familyCode');
    }

    public function it_should_return_family_code_with_truncate(): void
    {
        $target = Target::fromString('sku');
        $family = FamilyProperty::fromNormalized([
            'type' => FamilyProperty::type(),
            'process' => [
                'type' => 'truncate',
                'operator' => Process::PROCESS_OPERATOR_LTE,
                'value' => 3,
            ]
        ]);

        $this->__invoke(
            $family,
            $target,
            $this->getProductProjection('familyCode'),
            'AKN-'
        )->shouldReturn('fam');
    }

    public function it_should_throw_an_error_if_family_code_is_too_small(): void
    {
        $target = Target::fromString('sku');
        $family = FamilyProperty::fromNormalized([
            'type' => FamilyProperty::type(),
            'process' => [
                'type' => 'truncate',
                'operator' => Process::PROCESS_OPERATOR_EQ,
                'value' => 4,
            ]
        ]);

        $this->shouldThrow(new UnableToTruncateException('AKN-fam', 'sku', 'fam'))->during(
            '__invoke', [
                $family,
                $target,
                $this->getProductProjection('fam'),
                'AKN-'
            ]
        );
    }

    public function it_should_not_throw_an_error_if_family_code_is_exactly_the_right_length(): void
    {
        $target = Target::fromString('sku');
        $family = FamilyProperty::fromNormalized([
            'type' => FamilyProperty::type(),
            'process' => [
                'type' => 'truncate',
                'operator' => Process::PROCESS_OPERATOR_EQ,
                'value' => 3,
            ]
        ]);

        $this->shouldNotThrow(UnableToTruncateException::class)->during(
            '__invoke', [
                $family,
                $target,
                $this->getProductProjection('fam'),
                'AKN-'
            ]
        );
    }

    private function getProductProjection(string $familyCode): ProductProjection
    {
        return new ProductProjection(null, true, $familyCode, []);
    }
}
