<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure\ProductFileDropping;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFileWithUnreadComments;
use Akeneo\SupplierPortal\Retailer\Infrastructure\Mailer\SendSymfonyEmail;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\SendTwigProductFilesWithUnreadCommentsForContributorEmail;
use PHPUnit\Framework\TestCase;

final class SendTwigProductFilesWithUnreadCommentsForContributorEmailTest extends TestCase
{
    /** @test */
    public function itBuildsASummaryEmail(): void
    {
        $productFilesWithUnreadComments = ([
                new ProductFileWithUnreadComments(
                    '7bae987a-7581-11ed-a1eb-0242ac120002',
                    'file.xlsx',
                    '/path',
                    [
                        ['test', 'julia@retailer.com'],
                        ['test 2', 'julia@retailer.com'],
                    ],
                ),
                new ProductFileWithUnreadComments(
                    '4d50d11a-761b-11ed-a1eb-0242ac120002',
                    'file.xlsx',
                    '/path',
                    [
                        ['test', 'julia@retailer.com'],
                        ['test 2', 'julia@retailer.com'],
                    ],
                ),
        ]);
        $contributorEmail = 'jimmy@contributor.com';

        $sendEmail = $this->createMock(SendSymfonyEmail::class);
        $sendEmail->expects($this->once())->method('__invoke');

        $sut = new SendTwigProductFilesWithUnreadCommentsForContributorEmail($sendEmail, '/assets');
        ($sut)($productFilesWithUnreadComments, $contributorEmail);
    }
}
