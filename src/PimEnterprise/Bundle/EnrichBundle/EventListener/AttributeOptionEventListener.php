<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\EventListener;

use PimEnterprise\Bundle\WorkflowBundle\Exception\PublishedProductConsistencyException;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Subscriber to manage permissions on attribute options
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class AttributeOptionEventListener
{
    /**
     * @param PublishedProductRepositoryInterface $publishedRepository
     */
    public function __construct(PublishedProductRepositoryInterface $publishedRepository)
    {
        $this->publishedRepository = $publishedRepository;
    }

    /**
     * On attribute deletion event
     *
     * @param GenericEvent $event
     *
     * @throws PublishedProductConsistencyException
     *
     * @return GenericEvent
     */
    public function onAttributeOptionDelete(GenericEvent $event)
    {
        $attributeOption = $event->getSubject();

        if ($this->publishedRepository->countPublishedProductsForAttributeOption($attributeOption) > 0) {
            throw new PublishedProductConsistencyException(
                "Impossible to remove an option that has been published in a product",
                400,
                null,
                'pim_enrich_attribute_edit',
                ['id' => $attributeOption->getId()]
            );
        }

        return $event;
    }
}
