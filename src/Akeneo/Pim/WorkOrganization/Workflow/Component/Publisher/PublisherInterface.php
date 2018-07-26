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

/**
 * Publisher interface
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
interface PublisherInterface
{
    /**
     * Publish the source object
     *
     * @param object $object
     * @param array  $options
     *
     * @return object
     */
    public function publish($object, array $options = []);

    /**
     * Checks whether the given class is supported for publishing by this publisher
     *
     * @param object $object
     *
     * @return bool
     */
    public function supports($object);
}
