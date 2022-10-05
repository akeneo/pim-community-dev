<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Application\ProductFileDropping\Subscriber;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Subscriber\NotifyUsersOnProductFileAdded;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Notifier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event\ProductFileAdded;
use Akeneo\SupplierPortal\Retailer\Test\Builder\ProductFileBuilder;
use PHPUnit\Framework\TestCase;

final class NotifyUsersOnProductFileAddedTest extends TestCase
{
    /** @test */
    public function itSubscribesToTheProductFileAddedEvent(): void
    {
        static::assertSame(
            [ProductFileAdded::class => 'notifyUsers'],
            NotifyUsersOnProductFileAdded::getSubscribedEvents(),
        );
    }

    /** @test */
    public function itNotifiesAllTheUsersWhenASupplierDropsAFile(): void
    {
        $notifier = $this->createMock(Notifier::class);
        $sut = new NotifyUsersOnProductFileAdded($notifier);

        $notifier
            ->expects($this->once())
            ->method('notifyUsersForProductFileAdding')
            ->with('contributor@example.com', 'Supplier label')
        ;

        $sut->notifyUsers(
            new ProductFileAdded(
                (new ProductFileBuilder())
                    ->withIdentifier('e12d4c68-8d25-4f6a-a989-1364b1bb4cbd')
                    ->withUploadedBySupplier('7f25bf84-9853-4b40-9930-1c34ec7594e6')
                    ->build(),
                'Supplier label',
            ),
        );
    }
}
