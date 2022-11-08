<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Domain\ProductFileDropping\Write\Event;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event\ProductFileCommentedBySupplier;
use PHPUnit\Framework\TestCase;

final class ProductFileCommentedBySupplierTest extends TestCase
{
    /** @test */
    public function itExposesTheProductFileIdentifierAndTheCommentContent(): void
    {
        $productFileCommentedBySupplier = new ProductFileCommentedBySupplier(
            '37c365eb-e18b-43b3-8a55-934bb4d2d7e7',
            'Here is a comment',
            'jimmy@supplier.com',
        );

        static::assertSame(
            '37c365eb-e18b-43b3-8a55-934bb4d2d7e7',
            $productFileCommentedBySupplier->productFileIdentifier(),
        );
        static::assertSame('Here is a comment', $productFileCommentedBySupplier->commentContent());
        static::assertSame('jimmy@supplier.com', $productFileCommentedBySupplier->authorEmail());
    }
}
