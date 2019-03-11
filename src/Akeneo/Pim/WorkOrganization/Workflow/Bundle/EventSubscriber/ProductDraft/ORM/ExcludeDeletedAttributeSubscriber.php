<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\EventSubscriber\ProductDraft\ORM;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\FindExistingAttributeCodesQuery;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Exclude from a product draft all unexisting attributes
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ExcludeDeletedAttributeSubscriber implements EventSubscriber
{
    /** @var FindExistingAttributeCodesQuery */
    private $query;

    public function __construct(FindExistingAttributeCodesQuery $query)
    {
        $this->query = $query;
    }

    /**
     * Specifies the list of events to listen
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return ['postLoad'];
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
    {
        $productDraft = $args->getObject();

        if (!$productDraft instanceof EntityWithValuesDraftInterface) {
            return;
        }

        $changes = $productDraft->getChanges();
        $codesToCheck = [];
        $types = [];
        foreach ($changes as $type => $attributes) {
            $codesToCheck = array_merge($codesToCheck, array_keys($attributes));
            $types[] = $type;
        }
        $codesToCheck = array_unique($codesToCheck);

        $existingAttributeCodes = $this->query->execute($codesToCheck);
        $deletedAttributeCodes = array_diff($codesToCheck, $existingAttributeCodes);

        foreach ($deletedAttributeCodes as $code) {
            foreach ($types as $type) {
                unset($changes[$type][$code]);
            }
        }

        $productDraft->setChanges($changes);
    }
}
