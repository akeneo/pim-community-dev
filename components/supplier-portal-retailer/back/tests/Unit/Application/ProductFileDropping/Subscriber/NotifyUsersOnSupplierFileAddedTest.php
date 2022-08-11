<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Application\ProductFileDropping\Subscriber;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Subscriber\NotifyUsersOnSupplierFileAdded;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Notifier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierLabelFromIdentifier;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Event\SupplierFileAdded;
use Akeneo\SupplierPortal\Supplier\Domain\ProductFileDropping\Write\Model\SupplierFile;
use PHPUnit\Framework\TestCase;

final class NotifyUsersOnSupplierFileAddedTest extends TestCase
{
    /** @test */
    public function itSubscribesToTheSupplierFileAddedEvent(): void
    {
        static::assertSame(
            [SupplierFileAdded::class => 'notifyUsers'],
            NotifyUsersOnSupplierFileAdded::getSubscribedEvents(),
        );
    }

    /** @test */
    public function itNotifiesAllTheUsersWhenASupplierDropsAFile(): void
    {
        $notifier = $this->createMock(Notifier::class);
        $getSupplierLabelFromIdentifier = $this->createMock(GetSupplierLabelFromIdentifier::class);
        $sut = new NotifyUsersOnSupplierFileAdded($notifier, $getSupplierLabelFromIdentifier);

        $getSupplierLabelFromIdentifier
            ->expects($this->once())
            ->method('__invoke')
            ->with('7f25bf84-9853-4b40-9930-1c34ec7594e6')
            ->willReturn('Los Pollos Hermanos')
        ;

        $notifier
            ->expects($this->once())
            ->method('notifyUsersForSupplierFileAdding')
            ->with('contributor@example.com')
        ;

        $sut->notifyUsers(new SupplierFileAdded(SupplierFile::create(
            'e12d4c68-8d25-4f6a-a989-1364b1bb4cbd',
            'file.xlsx',
            'path/to/file.xlsx',
            'contributor@example.com',
            '7f25bf84-9853-4b40-9930-1c34ec7594e6',
        )));
    }
}
