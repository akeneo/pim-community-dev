<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Application\Supplier\Validation;

use Akeneo\OnboarderSerenity\Application\Supplier\UpdateSupplier;
use Akeneo\OnboarderSerenity\Application\Supplier\Validation\UniqueContributorEmail;
use Akeneo\OnboarderSerenity\Application\Supplier\Validation\UniqueContributorEmailValidator;
use Akeneo\OnboarderSerenity\Domain\Supplier\Read\SupplierContributorsBelongToAnotherSupplier;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

final class UniqueContributorEmailValidatorTest extends TestCase
{
    /** @test */
    public function itAddsAViolationIfTheContributorAlreadyBelongToAnotherSupplier(): void
    {
        $updateSupplierCommand = new UpdateSupplier(
            '36fc4dbf-43cb-4246-8966-56ca111d859d',
            'My supplier',
            ['contributor1@akeneo.com'],
        );

        $executionContext = $this->createMock(ExecutionContextInterface::class);
        $violationsBuilder = $this->createMock(ConstraintViolationBuilderInterface::class);
        $executionContext->expects($this->once())->method('getObject')->willReturn($updateSupplierCommand);
        $executionContext->expects($this->once())->method('buildViolation')->willReturn($violationsBuilder);
        $violationsBuilder->expects($this->once())->method('setParameter')->willReturn($violationsBuilder);
        $violationsBuilder->expects($this->once())->method('addViolation');

        $spy = $this->createMock(SupplierContributorsBelongToAnotherSupplier::class);

        $spy->expects($this->once())->method('__invoke')->willReturn(
            ['contributor1@akeneo.com'],
        );

        $sut = new UniqueContributorEmailValidator($spy);
        $sut->initialize($executionContext);
        $sut->validate('contributor1@akeneo.com', new UniqueContributorEmail());
    }

    /** @test */
    public function itDoesNotAddAViolationIfTheContributorIsUnique(): void
    {
        $updateSupplierCommand = new UpdateSupplier(
            '36fc4dbf-43cb-4246-8966-56ca111d859d',
            'My supplier',
            ['contributor1@akeneo.com'],
        );

        $executionContext = $this->createMock(ExecutionContextInterface::class);
        $executionContext->expects($this->once())->method('getObject')->willReturn($updateSupplierCommand);
        $executionContext->expects($this->never())->method('buildViolation');

        $spy = $this->createMock(SupplierContributorsBelongToAnotherSupplier::class);

        $spy->expects($this->once())->method('__invoke')->willReturn([]);

        $sut = new UniqueContributorEmailValidator($spy);
        $sut->initialize($executionContext);
        $sut->validate('contributor1@akeneo.com', new UniqueContributorEmail());
    }
}
