<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Publisher;

use Pim\Component\Catalog\Model\AttributeOptionInterface;

/**
 * Attribute option publisher
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
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
        return $object instanceof AttributeOptionInterface;
    }
}
