<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Test\Unit\Infrastructure\Supplier\Import;

use Akeneo\OnboarderSerenity\Infrastructure\Supplier\Import\FlatToStandard;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use PHPUnit\Framework\TestCase;

final class FlatToStandardUnit extends TestCase
{
    /** @test */
    public function it_converts_flat_supplier_data_into_structured_data(): void
    {
        $flatSupplierData = [
            'supplier_code' => 42,
            'supplier_label' => 'Foo',
            'contributor_emails' => 'foo@foo.foo,foo2@foo.foo',
        ];

        $fieldRequirementsCheckerMock = $this->createMock(FieldsRequirementChecker::class);
        $fieldRequirementsCheckerMock
            ->expects($this->once())
            ->method('checkFieldsPresence')
            ->with($flatSupplierData, ['supplier_code', 'supplier_label', 'contributor_emails'])
        ;
        $fieldRequirementsCheckerMock
            ->expects($this->once())
            ->method('checkFieldsFilling')
            ->with($flatSupplierData, ['supplier_code', 'supplier_label'])
        ;
        $sut = new FlatToStandard($fieldRequirementsCheckerMock);

        $structuredSupplierData = $sut->convert($flatSupplierData);

        static::assertSame('42', $structuredSupplierData['supplier_code']);
        static::assertSame('Foo', $structuredSupplierData['supplier_label']);
        static::assertSame(['foo@foo.foo', 'foo2@foo.foo'], $structuredSupplierData['contributor_emails']);
    }

    /** @test */
    public function it_converts_flat_supplier_data_into_structured_data_when_there_is_no_contributors(): void
    {
        $flatSupplierData = [
            'supplier_code' => 42,
            'supplier_label' => 'Foo',
            'contributor_emails' => '',
        ];

        $fieldRequirementsCheckerMock = $this->createMock(FieldsRequirementChecker::class);
        $fieldRequirementsCheckerMock
            ->expects($this->once())
            ->method('checkFieldsPresence')
            ->with($flatSupplierData, ['supplier_code', 'supplier_label', 'contributor_emails'])
        ;
        $fieldRequirementsCheckerMock
            ->expects($this->once())
            ->method('checkFieldsFilling')
            ->with($flatSupplierData, ['supplier_code', 'supplier_label'])
        ;
        $sut = new FlatToStandard($fieldRequirementsCheckerMock);

        $structuredSupplierData = $sut->convert($flatSupplierData);

        static::assertSame('42', $structuredSupplierData['supplier_code']);
        static::assertSame('Foo', $structuredSupplierData['supplier_label']);
        static::assertSame([], $structuredSupplierData['contributor_emails']);
    }
}
