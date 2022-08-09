<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Test\Unit\Domain\ProductFileDropping\Read\Model;

use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Read\Model\ProductFilePathAndFileName;
use PHPUnit\Framework\TestCase;

final class ProductFilePathAndFileNameTest extends TestCase
{
    /** @test */
    public function itOnlyContainsTheFilenameAndThePath(): void
    {
        $resetPasswordRequestedReflectionClass = new \ReflectionClass(ProductFilePathAndFileName::class);
        $properties = $resetPasswordRequestedReflectionClass->getProperties();

        static::assertCount(2, $properties);
        static::assertSame(
            'originalFilename',
            $properties[0]->getName(),
        );
        static::assertSame(
            'path',
            $properties[1]->getName(),
        );
    }
}
