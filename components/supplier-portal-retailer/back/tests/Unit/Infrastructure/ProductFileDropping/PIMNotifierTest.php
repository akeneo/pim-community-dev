<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Test\Unit\Infrastructure\ProductFileDropping;

use Akeneo\Platform\Bundle\NotificationBundle\Entity\Notification;
use Akeneo\Platform\Bundle\NotificationBundle\Notifier;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\PIMNotifier;
use Akeneo\Test\Acceptance\User\InMemoryUserRepository;
use Akeneo\UserManagement\Component\Model\User;
use PHPUnit\Framework\TestCase;

final class PIMNotifierTest extends TestCase
{
    /** @test */
    public function itNotifiesAllTheUsersWhenASupplierDroppedAFile(): void
    {
        $notifier = $this->createMock(Notifier::class);
        $userRepository = new InMemoryUserRepository();
        $julia = (new User())->setUsername('julia');
        $mary = (new User())->setUsername('mary');
        $userRepository->save($julia);
        $userRepository->save($mary);
        $sut = new PIMNotifier($notifier, $userRepository);

        $notifier
            ->expects($this->once())
            ->method('notify')
            ->with(
                $this->isInstanceOf(Notification::class),
                ['julia' => $julia, 'mary' => $mary],
            )
        ;

        $sut->notifyUsersForProductFileAdding('contributor@example.com', 'Los Pollos Hermanos');
    }
}
