<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Event;

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Change set event
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class ChangeSetEvent extends Event
{
    /** @var ProductValueInterface */
    protected $value;

    /** @var null|array */
    protected $changeSet;

    /**
     * @param ProductValueInterface $value
     * @param null|array            $changeSet
     */
    public function __construct(ProductValueInterface $value, $changeSet)
    {
        $this->value = $value;
        $this->changeSet = $changeSet;
    }

    /**
     * Get the value
     *
     * @return ProductValueInterface
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Get the submitted changeSet
     *
     * @return array
     */
    public function getChangeSet()
    {
        return $this->changeSet;
    }

    /**
     * Overrides the submitted changeSet
     *
     * @param array $changeSet
     */
    public function setChangeSet(array $changeSet)
    {
        $this->changeSet = $changeSet;
    }
}
