<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Application\ProductFileDropping\Write\CommentProductFileHandlerForSupplier;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CommentProductFileForSupplier\CommentProductFileForSupplier;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\CommentProductFileForSupplier\CommentProductFileHandlerForSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use PHPUnit\Framework\TestCase;
use Psr\Log\Test\TestLogger;

final class CommentProductFileHandlerForSupplierTest extends TestCase
{
    /** @test */
    public function itLogsWhenAContributorCommentedAProductFile(): void
    {
        $logger = new TestLogger();
        $productFileRepository = $this->createMock(ProductFileRepository::class);

        $productFileRepository
            ->expects($this->once())
            ->method('find')
            ->with(Identifier::fromString('13e9a59b-eecc-4f91-9833-6c649aac2d59'))
            ->willReturn(
                (new ProductFileBuilder())->withIdentifier('13e9a59b-eecc-4f91-9833-6c649aac2d59')->build(),
            )
        ;

        $sut = new CommentProductFileHandlerForSupplier($productFileRepository, $logger);

        ($sut)(new CommentProductFileForSupplier(
            '13e9a59b-eecc-4f91-9833-6c649aac2d59',
            'jimmy@supplier.com',
            'Here is a comment',
            new \DateTimeImmutable(),
        ));

        static::assertTrue($logger->hasInfo([
            'message' => 'Contributor "jimmy@supplier.com" commented a product file.',
            'context' => [
                'data' => [
                    'identifier' => '13e9a59b-eecc-4f91-9833-6c649aac2d59',
                    'content' => 'Here is a comment',
                    'metric_key' => 'contributor_product_file_commented',
                ],
            ],
        ]));
    }
}
