<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Application\ProductFileDropping\Write\CommentProductFileHandlerForSupplier;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CommentProductFileForSupplier\CommentProductFileForSupplier;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CommentProductFileForSupplier\CommentProductFileHandlerForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event\ProductFileCommentedBySupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\Identifier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class CommentProductFileHandlerForSupplierTest extends TestCase
{
    /** @test */
    public function itDispatchesAProductFileCommentedBySupplierEventWhenAContributorCommentedAProductFile(): void
    {
        $productFileRepository = $this->createMock(ProductFileRepository::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $productFileRepository
            ->expects($this->once())
            ->method('find')
            ->with(Identifier::fromString('13e9a59b-eecc-4f91-9833-6c649aac2d59'))
            ->willReturn(
                (new ProductFileBuilder())->withIdentifier('13e9a59b-eecc-4f91-9833-6c649aac2d59')->build(),
            );
        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(new ProductFileCommentedBySupplier(
                '13e9a59b-eecc-4f91-9833-6c649aac2d59',
                'Here is a comment',
                'jimmy@supplier.com',
            ));

        $sut = new CommentProductFileHandlerForSupplier($productFileRepository, $eventDispatcher);

        ($sut)(new CommentProductFileForSupplier(
            '13e9a59b-eecc-4f91-9833-6c649aac2d59',
            'jimmy@supplier.com',
            'Here is a comment',
            new \DateTimeImmutable(),
        ));
    }
}
