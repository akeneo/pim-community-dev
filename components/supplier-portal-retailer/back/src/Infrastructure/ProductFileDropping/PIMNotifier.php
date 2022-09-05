<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping;

use Akeneo\Platform\Bundle\NotificationBundle\Entity\Notification;
use Akeneo\Platform\Bundle\NotificationBundle\NotifierInterface;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Notifier;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;

final class PIMNotifier implements Notifier
{
    public function __construct(private NotifierInterface $notifier, private UserRepositoryInterface $userRepository)
    {
    }

    public function notifyUsersForProductFileAdding(string $contributorEmail, string $supplierLabel): void
    {
        $notification = new Notification();
        $notification
            ->setType('add')
            ->setMessage('supplier_portal.product_file_dropping.pim_notification.content')
            ->setMessageParams(['{{ contributorEmail }}' => $contributorEmail, '{{ supplierLabel }}' => $supplierLabel])
            ->setRoute('supplier_portal_retailer_product_files_list')
            ->setContext([
                'actionType' => 'new_product_file',
                'buttonLabel' => 'pim_notification.supplier_portal.new_product_file.button_label',
            ])
        ;

        $this->notifier->notify($notification, $this->userRepository->findAll());
    }
}
