<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Application\Supplier;

use Akeneo\OnboarderSerenity\Application\Supplier\Exception\InvalidData;
use Akeneo\OnboarderSerenity\Application\Supplier\Exception\SupplierDoesNotExist;
use Akeneo\OnboarderSerenity\Application\Supplier\UpdateSupplier;
use Akeneo\OnboarderSerenity\Application\Supplier\UpdateSupplierHandler;
use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Repository\InMemory\InMemoryRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UpdateSupplierHandlerTest extends TestCase
{
    /** @test */
    public function itUpdatesASupplierWithoutAnyError(): void
    {
        $identifier = \Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Identifier::fromString('01319d4c-81c4-4f60-a992-41ea3546824c');

        $command = new UpdateSupplier((string) $identifier, 'Updated label', ['contributor1@example.com', 'contributor2@example.com']);

        $validatorSpy = $this->getValidatorSpyWithNoError($command);

        $repository = new InMemoryRepository();
        $repository->save(\Akeneo\OnboarderSerenity\Domain\Supplier\Write\Model\Supplier::create((string) $identifier, 'code', 'label', []));

        $handler = new UpdateSupplierHandler($repository, $validatorSpy);
        ($handler)($command);

        $supplier = $repository->find($identifier);
        static::assertSame('Updated label', $supplier->label());
        static::assertSame([
            ['email' => 'contributor1@example.com'],
            ['email' => 'contributor2@example.com'],
        ], $supplier->contributors());
        $this->assertEquals(2, $repository->saveCallCounter);
    }

    /** @test */
    public function itThrowsAnExceptionIfTheCommandIsInvalid(): void
    {
        $identifier = \Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Identifier::fromString('01319d4c-81c4-4f60-a992-41ea3546824c');
        $command = new UpdateSupplier(
            (string) $identifier,
            str_repeat('a', 201),
            ['contributor1@example.com', 'invalidEmail', 'contributor2@example.com'],
        );

        $violationsSpy = $this->createMock(ConstraintViolationList::class);
        $violationsSpy->expects($this->once())->method('count')->willReturn(1);

        $validatorSpy = $this->createMock(ValidatorInterface::class);
        $validatorSpy->expects($this->once())->method('validate')->with($command)->willReturn($violationsSpy);

        $this->expectExceptionObject(new InvalidData($violationsSpy));

        $handler = new UpdateSupplierHandler(new InMemoryRepository(), $validatorSpy);
        ($handler)($command);
    }

    /** @test */
    public function itThrowsAnExceptionIfTheSupplierDoesNotExist(): void
    {
        $identifier = \Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Identifier::fromString('01319d4c-81c4-4f60-a992-41ea3546824c');

        $command = new UpdateSupplier(
            (string) $identifier,
            'Updated label',
            ['contributor1@example.com', 'contributor2@example.com'],
        );

        $this->expectExceptionObject(new SupplierDoesNotExist());

        $handler = new UpdateSupplierHandler(new InMemoryRepository(), $this->getValidatorSpyWithNoError($command));
        ($handler)($command);
    }

    private function getValidatorSpyWithNoError(UpdateSupplier $command): ValidatorInterface
    {
        $violationsSpy = $this->createMock(ConstraintViolationList::class);
        $violationsSpy->expects($this->once())->method('count')->willReturn(0);

        $validatorSpy = $this->createMock(ValidatorInterface::class);
        $validatorSpy->expects($this->once())->method('validate')->with($command)->willReturn($violationsSpy);

        return $validatorSpy;
    }
}
