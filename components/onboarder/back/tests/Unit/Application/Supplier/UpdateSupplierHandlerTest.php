<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Application\Supplier;

use Akeneo\OnboarderSerenity\Application\Supplier\Exception\InvalidDataException;
use Akeneo\OnboarderSerenity\Application\Supplier\UpdateSupplier;
use Akeneo\OnboarderSerenity\Application\Supplier\UpdateSupplierHandler;
use Akeneo\OnboarderSerenity\Domain\Write\Supplier;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UpdateSupplierHandlerTest extends TestCase
{
    /** @test */
    public function itUpdatesASupplierWithoutAnyError(): void
    {
        $identifier = Supplier\ValueObject\Identifier::fromString('01319d4c-81c4-4f60-a992-41ea3546824c');

        $command = new UpdateSupplier((string) $identifier, 'Updated label', ['contributor1@akeneo.com', 'contributor2@akeneo.com']);

        $validatorSpy = $this->getValidatorSpyWithNoError($command);

        $repository = new InMemoryRepository();
        $repository->save(Supplier\Model\Supplier::create((string) $identifier, 'code', 'label', []));

        $handler = new UpdateSupplierHandler($repository, $validatorSpy);
        ($handler)($command);

        $supplier = $repository->find($identifier);
        static::assertSame('Updated label', $supplier->label());
        static::assertSame(['contributor1@akeneo.com', 'contributor2@akeneo.com'], $supplier->contributors()->toArray());
        $this->assertSame(2, $repository->saveCallCounter);
    }

    /** @test */
    public function itThrowsAnExceptionIfTheCommandIsInvalid(): void
    {
        $identifier = Supplier\ValueObject\Identifier::fromString('01319d4c-81c4-4f60-a992-41ea3546824c');

        $command = new UpdateSupplier((string) $identifier, 'Updated label', ['contributor1@akeneo.com', 'contributor2@akeneo.com']);

        $violationsSpy = $this->createMock(ConstraintViolationListInterface::class);
        $violationsSpy->expects($this->once())->method('count')->willReturn(1);

        $validatorSpy = $this->createMock(ValidatorInterface::class);
        $validatorSpy->expects($this->once())->method('validate')->with($command)->willReturn($violationsSpy);

        $this->expectExceptionObject(new InvalidDataException($violationsSpy));

        $handler = new UpdateSupplierHandler(new InMemoryRepository(), $validatorSpy);
        ($handler)($command);
    }

    /** @test */
    public function itDoesNothingIfTheSupplierDoesNotExist(): void
    {
        $identifier = Supplier\ValueObject\Identifier::fromString('01319d4c-81c4-4f60-a992-41ea3546824c');

        $command = new UpdateSupplier((string) $identifier, 'Updated label', ['contributor1@akeneo.com', 'contributor2@akeneo.com']);

        $repository = new InMemoryRepository();
        $handler = new UpdateSupplierHandler($repository, $this->getValidatorSpyWithNoError($command));

        ($handler)($command);

        $this->assertSame(0, $repository->saveCallCounter);
    }

    private function getValidatorSpyWithNoError(UpdateSupplier $command): ValidatorInterface
    {
        $violationsSpy = $this->createMock(ConstraintViolationListInterface::class);
        $violationsSpy->expects($this->once())->method('count')->willReturn(0);

        $validatorSpy = $this->createMock(ValidatorInterface::class);
        $validatorSpy->expects($this->once())->method('validate')->with($command)->willReturn($violationsSpy);

        return $validatorSpy;
    }
}
