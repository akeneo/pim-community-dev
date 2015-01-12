<?php

namespace Pim\Bundle\CatalogBundle\Factory;

use Pim\Bundle\CatalogBundle\Model\GroupTypeInterface;

/**
 * Group factory
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupFactory
{
    /** @var string */
    protected $metricClass;

    /**
     * @param string $groupClass
     */
    public function __construct($groupClass)
    {
        $this->groupClass = $groupClass;
    }

    /**
     * Create and configure a group instance
     *
     * @param GroupTypeInterface $groupType
     *
     * @return \Pim\Bundle\CatalogBundle\Model\GroupInterface
     */
    public function createGroup(GroupTypeInterface $groupType = null)
    {
        $group = new $this->groupClass();
        if ($groupType) {
            $group->setType($groupType);
        }

        return $group;
    }
}
