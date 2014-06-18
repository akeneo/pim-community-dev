<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher;

use Pim\Bundle\CatalogBundle\Entity\AttributeOption;

/**
 * Attribute option publisher
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeOptionPublisher implements PublisherInterface
{
    /**
     * {@inheritdoc}
     */
    public function publish($object, array $options = [])
    {
        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof AttributeOption;
    }
}
