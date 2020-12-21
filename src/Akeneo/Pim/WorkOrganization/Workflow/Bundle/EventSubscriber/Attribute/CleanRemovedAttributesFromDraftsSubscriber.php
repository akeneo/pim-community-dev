<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\Attribute;

use Akeneo\Pim\Structure\Bundle\Event\AttributeEvents;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Cleaner\RemovedAttributeCleaner;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Removes changes on deleted attributes in all products and product models drafts
 */
final class CleanRemovedAttributesFromDraftsSubscriber implements EventSubscriberInterface
{
    private RemovedAttributeCleaner $removedAttributeCleaner;

    public function __construct(RemovedAttributeCleaner $removedAttributeCleaner)
    {
        $this->removedAttributeCleaner = $removedAttributeCleaner;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            AttributeEvents::POST_CLEAN => 'saveAffectedDrafts',
        ];
    }

    /**
     * Find product and product model drafts containing changes on removed attributes and save them.
     */
    public function saveAffectedDrafts(): void
    {
        $this->removedAttributeCleaner->cleanAffectedDrafts();
    }
}
