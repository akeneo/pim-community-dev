<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Publisher;

use Akeneo\Pim\Enrichment\Component\Product\Model\ReferenceDataInterface;

/**
 * Reference data publisher
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class ReferenceDataPublisher implements PublisherInterface
{
    /**
     * {@inheritdoc}
     */
    public function publish($referenceData, array $options = [])
    {
        return $referenceData;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object)
    {
        return $object instanceof ReferenceDataInterface;
    }
}
