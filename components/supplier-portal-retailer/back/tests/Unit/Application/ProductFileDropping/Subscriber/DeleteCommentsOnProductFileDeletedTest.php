<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Application\ProductFileDropping\Subscriber;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Subscriber\DeleteCommentsOnProductFileDeleted;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event\ProductFileDeleted;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use PHPUnit\Framework\TestCase;

final class DeleteCommentsOnProductFileDeletedTest extends TestCase
{
    /** @test */
    public function itSubscribesToTheProductFileDeletedEvent(): void
    {
        static::assertSame(
            [ProductFileDeleted::class => 'deleteComments'],
            DeleteCommentsOnProductFileDeleted::getSubscribedEvents(),
        );
    }

    /** @test */
    public function itDeletesTheProductFileCommentWhenAProductFileIsDeleted(): void
    {
        $productFileRepository = $this->createMock(ProductFileRepository::class);
        $sut = new DeleteCommentsOnProductFileDeleted($productFileRepository);

        $productFileRepository
            ->expects($this->once())
            ->method('deleteProductFileRetailerComments')
            ->with('35e11e7b-6f25-49f0-a720-610eb067ee6b')
        ;
        $productFileRepository
            ->expects($this->once())
            ->method('deleteProductFileSupplierComments')
            ->with('35e11e7b-6f25-49f0-a720-610eb067ee6b')
        ;

        $sut->deleteComments(new ProductFileDeleted('35e11e7b-6f25-49f0-a720-610eb067ee6b'));
    }
}
