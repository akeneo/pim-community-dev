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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Install\EventSubscriber;

use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Install\Query\CreateTableAttributeAddedToFamilyQuery;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Install\Query\CreateTableAttributeCreatedQuery;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvent;
use Akeneo\Platform\Bundle\InstallerBundle\Event\InstallerEvents;
use Doctrine\DBAL\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class InitFranklinInsightsDbSchemaSubscriber implements EventSubscriberInterface
{
    private $dbalConnection;

    public function __construct(Connection $dbalConnection)
    {
        $this->dbalConnection = $dbalConnection;
    }

    public static function getSubscribedEvents()
    {
        return [
            InstallerEvents::POST_DB_CREATE => 'initDbSchema'
        ];
    }

    public function initDbSchema(InstallerEvent $event): void
    {
        $this->dbalConnection->executeQuery(CreateTableAttributeCreatedQuery::QUERY);
        $this->dbalConnection->executeQuery(CreateTableAttributeAddedToFamilyQuery::QUERY);
    }
}
