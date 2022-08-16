<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileDropping\Read\Model;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFileNameAndResourceFile;
use PHPUnit\Framework\TestCase;

final class ProductFileNameAndResourceFileTest extends TestCase
{
    /** @test */
    public function itOnlyContainsTheFilenameAndTheResourceFile(): void
    {
        $resetPasswordRequestedReflectionClass = new \ReflectionClass(ProductFileNameAndResourceFile::class);
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
