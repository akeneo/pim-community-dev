<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\EventSubscriber;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class LoadRawTableConfiguration implements EventSubscriber
{
    private TableConfigurationRepository $tableConfigurationRepository;

    public function __construct(TableConfigurationRepository $tableConfigurationRepository)
    {
        $this->tableConfigurationRepository = $tableConfigurationRepository;
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::postLoad
        ];
    }

    public function postLoad(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();
        if (!$entity instanceof AttributeInterface || AttributeTypes::TABLE !== $entity->getType()) {
            return;
        }

        $tableConfiguration = $this->tableConfigurationRepository->getByAttributeId((int) $entity->getId());
        $entity->setRawTableConfiguration($tableConfiguration->normalize());
    }
}
