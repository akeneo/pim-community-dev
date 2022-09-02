<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\SuccessMetrics\Subscriber;

use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Event\ContributorAdded;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class LogOnContributorAdded implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ContributorAdded::class => 'logOnContributorAdded',
        ];
    }

    public function logOnContributorAdded(ContributorAdded $contributorAdded): void
    {
        $this->logger->info(
            sprintf(
                'Contributor "%s" created.',
                $contributorAdded->contributorEmail(),
            ),
            [
                'data' => [
                    'identifier' => (string) $contributorAdded->supplierIdentifier(),
                    'metric_key' => 'contributor_added',
                    'supplier_code' => $contributorAdded->supplierCode(),
                ],
            ],
        );
    }
}
