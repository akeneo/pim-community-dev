<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Filter;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Tool\Component\Batch\Model\JobInstance;

/**
 * Job instance filter for edition
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class JobInstanceEditRightFilter extends AbstractAuthorizationFilter implements
    CollectionFilterInterface,
    ObjectFilterInterface
{
    /**
     * {@inheritdoc}
     */
    public function filterObject($jobInstance, $type, array $options = [])
    {
        if (!$this->supportsObject($jobInstance, $type, $options)) {
            throw new \LogicException('This filter only handles objects of type "JobInstance"');
        }

        return !$this->authorizationChecker->isGranted(
            Attributes::EDIT,
            $jobInstance
        );
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return parent::supportsObject($options, $type, $options) && $object instanceof JobInstance;
    }
}
