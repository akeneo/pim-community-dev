<?php

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Handler;

/**
 * Product publisher/unpublisher handler.
 * It handles mass-publish or mass-unpublish actions.
 *
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
interface ProductPublisherHandlerInterface
{
    /**
     * Executes
     *
     * @param array $configuration
     */
    public function execute(array $configuration);
}
