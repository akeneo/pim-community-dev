<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure\ProductFileDropping\Job;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\GetProductFilesWithUnreadCommentsForContributor;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Model\ProductFileWithUnreadComments;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\SendProductFilesWithUnreadCommentsForContributorEmail;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Job\SendProductFilesWithUnreadCommentsForContributorEmail as SendSummaryEmail;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetAllSuppliersWithContributors;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\Model\SupplierWithContributors;
use PHPUnit\Framework\TestCase;

final class SendProductFilesWithUnreadCommentsForContributorEmailTest extends TestCase
{
    /** @test */
    public function itCallsTheSendProductFilesWithUnreadCommentsForContributorEmailService(): void
    {
        $contributorEmail = 'jimmy1@contributor.com';
        $productFiles =
            [
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
        ];

        $getProductFilesWithUnreadCommentsForContributor = $this->createMock(
            GetProductFilesWithUnreadCommentsForContributor::class,
        );
        $getAllSuppliersWithContributors = $this->createMock(GetAllSuppliersWithContributors::class);

        $getAllSuppliersWithContributors
            ->expects($this->once())
            ->method('__invoke')
            ->willReturn([
                new SupplierWithContributors(
                    '99d91436-7704-11ed-a1eb-0242ac120002',
                    'code',
                    'label',
                    ['jimmy1@contributor.com', 'jimmy2@contributor.com'],
                ),
            ])
        ;

        $getProductFilesWithUnreadCommentsForContributor
            ->expects($this->exactly(2))
            ->method('__invoke')
            ->withConsecutive(['jimmy1@contributor.com'], ['jimmy2@contributor.com'])
            ->willReturnOnConsecutiveCalls(
                [
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
                ],
                [],
            );

        $sendProductFilesWithUnreadCommentsForContributorEmailSpy = $this->createMock(
            SendProductFilesWithUnreadCommentsForContributorEmail::class,
        );
        $sendProductFilesWithUnreadCommentsForContributorEmailSpy
            ->expects($this->once())
            ->method('__invoke')
            ->with($productFiles, $contributorEmail);

        $sut = new SendSummaryEmail(
            $sendProductFilesWithUnreadCommentsForContributorEmailSpy,
            $getProductFilesWithUnreadCommentsForContributor,
            $getAllSuppliersWithContributors,
        );

        $sut->sendProductFilesWithUnreadCommentsForContributorEmail();
    }
}
