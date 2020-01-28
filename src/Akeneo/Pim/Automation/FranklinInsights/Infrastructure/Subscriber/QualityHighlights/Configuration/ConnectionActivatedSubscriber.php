<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\QualityHighlights\Configuration;

use Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights\PushStructureAndProductsToFranklin;
use Akeneo\Pim\Automation\FranklinInsights\Application\QualityHighlights\SchedulePushStructureAndProductsToFranklinInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Configuration\Event\ConnectionActivated;
use Akeneo\Pim\Automation\FranklinInsights\Domain\QualityHighlights\ValueObject\BatchSize;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ConnectionActivatedSubscriber implements EventSubscriberInterface
{
    /** @var SchedulePushStructureAndProductsToFranklinInterface */
    private $schedulePushStructureAndProductsToFranklin;

    public function __construct(SchedulePushStructureAndProductsToFranklinInterface $schedulePushStructureAndProductsToFranklin)
    {
        $this->schedulePushStructureAndProductsToFranklin = $schedulePushStructureAndProductsToFranklin;
    }

    public static function getSubscribedEvents()
    {
        return [
            ConnectionActivated::EVENT_NAME => 'pushStructureAndProducts',
        ];
    }

    public function pushStructureAndProducts(ConnectionActivated $event)
    {
        $this->schedulePushStructureAndProductsToFranklin->schedule(
            new BatchSize(PushStructureAndProductsToFranklin::DEFAULT_ATTRIBUTES_BATCH_SIZE),
            new BatchSize(PushStructureAndProductsToFranklin::DEFAULT_FAMILIES_BATCH_SIZE),
            new BatchSize(PushStructureAndProductsToFranklin::DEFAULT_PRODUCTS_BATCH_SIZE)
        );
    }
}
