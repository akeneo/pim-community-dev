<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Test\Unit\Application\Authentication\ContributorAccount\Validation;

use Akeneo\OnboarderSerenity\Retailer\Application\Authentication\ContributorAccount\Validation\Password;
use Akeneo\OnboarderSerenity\Retailer\Application\Authentication\ContributorAccount\Validation\PasswordValidator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class PasswordValidatorTest extends TestCase
{
    /** @test */
    public function itBuildsAViolationIfThePasswordLengthIsLessThanEightCharacters(): void
    {
        $executionContext = $this->createMock(ExecutionContextInterface::class);
        $violationsBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $executionContext
            ->expects($this->once())
            ->method('buildViolation')
            ->with('onboarder.supplier.contributor_account.validation.min_password_length')
            ->willReturn($violationsBuilder)
        ;
        $violationsBuilder->expects($this->once())->method('addViolation');

        $sut = new PasswordValidator();
        $sut->initialize($executionContext);
        $sut->validate('Foo1', new Password());
    }

    /** @test */
    public function itBuildsAViolationIfThePasswordLengthExceedTwoHundredsFiftyFiveCharacters(): void
    {
        $executionContext = $this->createMock(ExecutionContextInterface::class);
        $violationsBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $executionContext
            ->expects($this->once())
            ->method('buildViolation')
            ->with('onboarder.supplier.contributor_account.validation.max_password_length')
            ->willReturn($violationsBuilder)
        ;
        $violationsBuilder->expects($this->once())->method('addViolation');

        $sut = new PasswordValidator();
        $sut->initialize($executionContext);
        $sut->validate(sprintf('1Aa%s', str_repeat('a', 253)), new Password());
    }

    /** @test */
    public function itBuildsAViolationIfThePasswordDoesNotContainAnUppercaseLetter(): void
    {
        $executionContext = $this->createMock(ExecutionContextInterface::class);
        $violationsBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $executionContext
            ->expects($this->once())
            ->method('buildViolation')
            ->with('onboarder.supplier.contributor_account.validation.should_contain_an_uppercase_letter')
            ->willReturn($violationsBuilder)
        ;
        $violationsBuilder->expects($this->once())->method('addViolation');

        $sut = new PasswordValidator();
        $sut->initialize($executionContext);
        $sut->validate('foofoofoo1', new Password());
    }

    /** @test */
    public function itBuildsAViolationIfThePasswordDoesNotContainALowercaseLetter(): void
    {
        $executionContext = $this->createMock(ExecutionContextInterface::class);
        $violationsBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $executionContext
            ->expects($this->once())
            ->method('buildViolation')
            ->with('onboarder.supplier.contributor_account.validation.should_contain_a_lowercase_letter')
            ->willReturn($violationsBuilder)
        ;
        $violationsBuilder->expects($this->once())->method('addViolation');

        $sut = new PasswordValidator();
        $sut->initialize($executionContext);
        $sut->validate('FOOFOOFOO1', new Password());
    }

    /** @test */
    public function itBuildsAViolationIfThePasswordDoesNotContainADigit(): void
    {
        $executionContext = $this->createMock(ExecutionContextInterface::class);
        $violationsBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $executionContext
            ->expects($this->once())
            ->method('buildViolation')
            ->with('onboarder.supplier.contributor_account.validation.should_contain_a_digit')
            ->willReturn($violationsBuilder)
        ;
        $violationsBuilder->expects($this->once())->method('addViolation');

        $sut = new PasswordValidator();
        $sut->initialize($executionContext);
        $sut->validate('Foo$AzeQSDBN', new Password());
    }
}
