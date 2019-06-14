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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\FranklinEvent;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\FranklinAttributeCreated;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Structure\Repository\FranklinAttributeCreatedRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class FranklinAttributeCreatedSubscriber implements EventSubscriberInterface
{
    private $repository;

    public function __construct(FranklinAttributeCreatedRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public static function getSubscribedEvents()
    {
        return [
            FranklinAttributeCreated::EVENT_NAME => 'persist'
        ];
    }

    public function persist(FranklinAttributeCreated $franklinAttributeCreated): void
    {
        $this->repository->save($franklinAttributeCreated);
    }
}
