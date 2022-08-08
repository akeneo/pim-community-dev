<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Domain\ProductFileDropping\Read\Model;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Read\Model\SupplierFileNameAndResourceFile;
use PHPUnit\Framework\TestCase;

final class SupplierFileNameAndResourceFileTest extends TestCase
{
    /** @test */
    public function itOnlyContainsTheFilenameAndTheResourceFile(): void
    {
        $resetPasswordRequestedReflectionClass = new \ReflectionClass(SupplierFileNameAndResourceFile::class);
        $properties = $resetPasswordRequestedReflectionClass->getProperties();

        static::assertCount(2, $properties);
        static::assertSame(
            'originalFilename',
            $properties[0]->getName(),
        );
        static::assertSame(
            'file',
            $properties[1]->getName(),
        );
    }
}
